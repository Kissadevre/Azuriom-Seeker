document.addEventListener('DOMContentLoaded', () => {
    const players = Array.from(document.querySelectorAll('[data-seeker-audio-player]'));
    const playbackRates = [1, 1.5, 2];

    const formatTime = (seconds) => {
        if (!Number.isFinite(seconds) || seconds < 0) {
            return '--:--';
        }

        const wholeSeconds = Math.floor(seconds);
        const minutes = Math.floor(wholeSeconds / 60);
        const remainder = wholeSeconds % 60;

        return `${minutes}:${String(remainder).padStart(2, '0')}`;
    };

    const seededPeaks = (seed, count) => {
        let value = Number(seed) || 1;

        return Array.from({ length: count }, (_, index) => {
            value = (value * 9301 + 49297 + index) % 233280;
            const curve = .58 + Math.sin(index * .41) * .16;

            return Math.max(.16, Math.min(1, (value / 233280) * .58 + curve * .42));
        });
    };

    players.forEach((player) => {
        const audio = player.querySelector('[data-seeker-audio]');
        const interfaceElement = player.querySelector('[data-seeker-audio-interface]');
        const playButton = player.querySelector('[data-seeker-audio-play]');
        const seekButton = player.querySelector('[data-seeker-audio-seek]');
        const speedButton = player.querySelector('[data-seeker-audio-speed]');
        const currentTime = player.querySelector('[data-seeker-audio-current]');
        const duration = player.querySelector('[data-seeker-audio-duration]');
        const canvas = player.querySelector('[data-seeker-audio-waveform]');

        if (!audio || !interfaceElement || !playButton || !seekButton || !speedButton || !canvas) {
            return;
        }

        const context = canvas.getContext('2d');
        let peaks = seededPeaks(player.dataset.mediaId, 72);
        let resizeFrame;

        const drawWaveform = () => {
            const bounds = canvas.getBoundingClientRect();

            if (bounds.width === 0 || bounds.height === 0 || !context) {
                return;
            }

            const ratio = window.devicePixelRatio || 1;
            canvas.width = Math.round(bounds.width * ratio);
            canvas.height = Math.round(bounds.height * ratio);
            context.setTransform(ratio, 0, 0, ratio, 0, 0);
            context.clearRect(0, 0, bounds.width, bounds.height);

            const progress = Number.isFinite(audio.duration) && audio.duration > 0
                ? audio.currentTime / audio.duration
                : 0;
            const gap = 2;
            const barWidth = Math.max(1.5, (bounds.width - gap * (peaks.length - 1)) / peaks.length);

            peaks.forEach((peak, index) => {
                const height = Math.max(4, peak * bounds.height);
                const x = index * (barWidth + gap);
                const y = (bounds.height - height) / 2;
                const played = (index + .5) / peaks.length <= progress;

                context.fillStyle = played ? '#726ee6' : 'rgba(77, 145, 190, .35)';
                if (typeof context.roundRect === 'function') {
                    context.beginPath();
                    context.roundRect(x, y, barWidth, height, barWidth / 2);
                    context.fill();
                } else {
                    context.fillRect(x, y, barWidth, height);
                }
            });
        };

        const sync = () => {
            const playing = !audio.paused && !audio.ended;
            const icon = playButton.querySelector('i');

            playButton.classList.toggle('is-playing', playing);
            playButton.setAttribute('aria-label', playing ? player.dataset.labelPause : player.dataset.labelPlay);
            icon.className = `bi bi-${playing ? 'pause' : 'play-fill'}`;
            currentTime.textContent = formatTime(audio.currentTime);
            duration.textContent = formatTime(audio.duration);
            seekButton.setAttribute('aria-label', `${player.dataset.labelSeek}: ${formatTime(audio.currentTime)} / ${formatTime(audio.duration)}`);
            drawWaveform();
        };

        const createWaveform = async () => {
            const source = audio.currentSrc || audio.querySelector('source')?.src;
            const AudioContext = window.AudioContext || window.webkitAudioContext;

            if (!source || !AudioContext) {
                return;
            }

            let audioContext;

            try {
                const response = await fetch(source, { credentials: 'same-origin' });

                if (!response.ok) {
                    return;
                }

                audioContext = new AudioContext();
                const buffer = await response.arrayBuffer();
                const decoded = await audioContext.decodeAudioData(buffer);
                const samples = decoded.getChannelData(0);
                const sampleSize = Math.max(1, Math.floor(samples.length / 72));
                const step = Math.max(1, Math.floor(sampleSize / 80));
                const measured = [];

                for (let bar = 0; bar < 72; bar += 1) {
                    let maximum = 0;
                    const start = bar * sampleSize;
                    const end = Math.min(samples.length, start + sampleSize);

                    for (let sample = start; sample < end; sample += step) {
                        maximum = Math.max(maximum, Math.abs(samples[sample]));
                    }

                    measured.push(maximum);
                }

                const maximum = Math.max(...measured, .01);
                peaks = measured.map((peak) => Math.max(.12, peak / maximum));
                drawWaveform();
            } catch (error) {
                // The deterministic waveform remains available when decoding is unsupported.
            } finally {
                audioContext?.close();
            }
        };

        playButton.addEventListener('click', async () => {
            if (audio.paused) {
                players.forEach((otherPlayer) => {
                    const otherAudio = otherPlayer.querySelector('[data-seeker-audio]');

                    if (otherAudio && otherAudio !== audio) {
                        otherAudio.pause();
                    }
                });

                try {
                    await audio.play();
                } catch (error) {
                    sync();
                }
            } else {
                audio.pause();
            }
        });

        seekButton.addEventListener('click', (event) => {
            if (!Number.isFinite(audio.duration) || audio.duration <= 0) {
                return;
            }

            const bounds = seekButton.getBoundingClientRect();
            const progress = Math.max(0, Math.min(1, (event.clientX - bounds.left) / bounds.width));
            audio.currentTime = progress * audio.duration;
            sync();
        });

        seekButton.addEventListener('keydown', (event) => {
            if (!Number.isFinite(audio.duration)) {
                return;
            }

            if (event.key === 'ArrowLeft' || event.key === 'ArrowRight') {
                event.preventDefault();
                audio.currentTime = Math.max(0, Math.min(audio.duration, audio.currentTime + (event.key === 'ArrowRight' ? 5 : -5)));
            } else if (event.key === 'Home' || event.key === 'End') {
                event.preventDefault();
                audio.currentTime = event.key === 'End' ? audio.duration : 0;
            } else {
                return;
            }

            sync();
        });

        speedButton.addEventListener('click', () => {
            const currentIndex = playbackRates.indexOf(audio.playbackRate);
            audio.playbackRate = playbackRates[(currentIndex + 1) % playbackRates.length];
            speedButton.textContent = `${audio.playbackRate}×`;
        });

        ['loadedmetadata', 'durationchange', 'timeupdate', 'play', 'pause', 'ended'].forEach((eventName) => {
            audio.addEventListener(eventName, sync);
        });

        const resizeObserver = 'ResizeObserver' in window
            ? new ResizeObserver(() => {
                window.cancelAnimationFrame(resizeFrame);
                resizeFrame = window.requestAnimationFrame(drawWaveform);
            })
            : null;

        resizeObserver?.observe(seekButton);
        window.addEventListener('resize', drawWaveform, { passive: true });

        audio.controls = false;
        audio.hidden = true;
        interfaceElement.hidden = false;
        sync();

        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver((entries) => {
                if (entries.some((entry) => entry.isIntersecting)) {
                    observer.disconnect();
                    createWaveform();
                }
            }, { rootMargin: '200px' });

            observer.observe(player);
        } else {
            createWaveform();
        }
    });
});
