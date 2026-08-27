@once
    @push('styles')
        <style>
            .seeker-admin-shell {
                --seeker-admin-radius: .9rem;
                --seeker-admin-soft-primary: rgba(var(--bs-primary-rgb), .1);
                --seeker-admin-soft-secondary: rgba(var(--bs-secondary-rgb), .07);
                --seeker-admin-accent: var(--bs-primary);
                --seeker-admin-accent-rgb: var(--bs-primary-rgb);
            }

            .container-fluid:has(> .seeker-admin-shell) > h1.h3.mb-3 {
                display: none;
            }

            .seeker-admin-header {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 1rem;
                margin-bottom: 1.5rem;
            }

            .seeker-admin-header.is-danger {
                --seeker-admin-accent: var(--bs-danger);
                --seeker-admin-accent-rgb: var(--bs-danger-rgb);
            }

            .seeker-admin-header.is-warning {
                --seeker-admin-accent: var(--bs-warning);
                --seeker-admin-accent-rgb: var(--bs-warning-rgb);
            }

            .seeker-admin-header.is-success {
                --seeker-admin-accent: var(--bs-success);
                --seeker-admin-accent-rgb: var(--bs-success-rgb);
            }

            .seeker-admin-heading {
                display: flex;
                min-width: 0;
                align-items: center;
                gap: .9rem;
            }

            .seeker-admin-heading h1 {
                margin-bottom: .25rem;
                font-size: calc(1.25rem + .35vw);
            }

            .seeker-admin-heading p {
                margin-bottom: 0;
                color: var(--bs-secondary-color);
            }

            .seeker-admin-eyebrow {
                display: block;
                margin-bottom: .2rem;
                color: var(--seeker-admin-accent);
                font-size: .7rem;
                font-weight: 800;
                letter-spacing: .075em;
                line-height: 1;
                text-transform: uppercase;
            }

            .seeker-admin-icon,
            .seeker-admin-stat-icon,
            .seeker-admin-empty-icon {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                flex: 0 0 auto;
                color: var(--bs-primary);
                background: var(--seeker-admin-soft-primary);
            }

            .seeker-admin-icon {
                width: 3rem;
                height: 3rem;
                border-radius: .8rem;
                color: var(--seeker-admin-accent);
                background: rgba(var(--seeker-admin-accent-rgb), .1);
                font-size: 1.35rem;
            }

            .seeker-admin-total {
                display: inline-flex;
                align-items: center;
                gap: .4rem;
                padding: .4rem .7rem;
                border: 1px solid rgba(var(--seeker-admin-accent-rgb), .15);
                border-radius: 999px;
                color: var(--seeker-admin-accent);
                background: rgba(var(--seeker-admin-accent-rgb), .1);
                font-size: .8rem;
                font-weight: 700;
                white-space: nowrap;
            }

            .seeker-admin-card {
                overflow: hidden;
                border: 1px solid rgba(var(--bs-primary-rgb), .1);
                border-radius: var(--seeker-admin-radius);
                box-shadow: 0 .3rem 1.25rem rgba(0, 0, 0, .045);
            }

            .seeker-admin-card > .card-header,
            .seeker-admin-card-header {
                padding: 1rem 1.25rem;
                border-bottom: 1px solid var(--bs-border-color);
                background: rgba(var(--bs-primary-rgb), .035);
            }

            .seeker-admin-card > .card-header h2,
            .seeker-admin-card > .card-header h3 {
                margin-bottom: 0;
                font-size: 1rem;
                font-weight: 700;
            }

            .seeker-admin-toolbar {
                padding: 1rem;
                border: 1px solid rgba(var(--bs-primary-rgb), .1);
                border-radius: var(--seeker-admin-radius);
                background: rgba(var(--bs-primary-rgb), .025);
                box-shadow: 0 .2rem .8rem rgba(0, 0, 0, .025);
            }

            .seeker-admin-toolbar .form-label {
                margin-bottom: .35rem;
            }

            .seeker-admin-filter-actions {
                display: flex;
                align-items: center;
                gap: .5rem;
            }

            .seeker-admin-filter-submit {
                padding-top: 1.95rem;
            }

            .seeker-admin-toolbar-title {
                display: flex;
                align-items: center;
                gap: .45rem;
                margin-bottom: .75rem;
                color: var(--bs-secondary-color);
                font-size: .76rem;
                font-weight: 700;
                letter-spacing: .04em;
                text-transform: uppercase;
            }

            .seeker-admin-table thead th {
                padding-top: .85rem;
                padding-bottom: .85rem;
                border-bottom-width: 1px;
                color: var(--bs-secondary-color);
                font-size: .72rem;
                font-weight: 700;
                letter-spacing: .045em;
                text-transform: uppercase;
                white-space: nowrap;
            }

            .seeker-admin-table tbody td {
                padding-top: .95rem;
                padding-bottom: .95rem;
                vertical-align: middle;
            }

            .seeker-admin-table tbody tr:last-child > td {
                border-bottom-width: 0;
            }

            .seeker-admin-table--actions th:last-child,
            .seeker-admin-table--actions td:last-child {
                position: sticky;
                right: 0;
                z-index: 1;
                background: var(--bs-body-bg);
                box-shadow: -.4rem 0 .65rem -.65rem rgba(0, 0, 0, .55);
            }

            .seeker-admin-table--actions thead th:last-child {
                z-index: 2;
                background: var(--bs-tertiary-bg);
            }

            .seeker-admin-table--actions.table-hover > tbody > tr:hover > td:last-child {
                background: var(--bs-tertiary-bg);
            }

            .seeker-admin-action-group {
                display: inline-flex;
                align-items: center;
                justify-content: flex-end;
                gap: .35rem;
            }

            .seeker-admin-action-group > .btn,
            .seeker-admin-action-group > form > .btn {
                display: inline-flex;
                width: 2.15rem;
                height: 2.15rem;
                align-items: center;
                justify-content: center;
                padding: 0;
                border-radius: .55rem;
            }

            .seeker-admin-date {
                display: flex;
                align-items: center;
                gap: .45rem;
            }

            .seeker-admin-date > i {
                width: 1rem;
                color: var(--bs-secondary-color);
                text-align: center;
            }

            .seeker-admin-report-indicator-table tbody td:first-child {
                border-left: .25rem solid transparent;
            }

            .seeker-admin-report-indicator-table tbody tr.seeker-admin-row-reported > td:first-child {
                border-left-color: var(--bs-warning);
            }

            .seeker-admin-status-toggle {
                cursor: pointer;
                font: inherit;
            }

            .seeker-admin-status-menu {
                min-width: 13rem;
            }

            .seeker-admin-status-menu .dropdown-item:disabled {
                opacity: 1;
            }

            .seeker-admin-empty {
                padding: 3rem 1rem !important;
                text-align: center;
            }

            .seeker-admin-empty-icon {
                width: 3.5rem;
                height: 3.5rem;
                margin-bottom: .85rem;
                border-radius: 1rem;
                color: var(--bs-secondary-color);
                background: var(--seeker-admin-soft-secondary);
                font-size: 1.45rem;
            }

            .seeker-admin-pagination {
                padding: 1rem 1.25rem;
                border-top: 1px solid var(--bs-border-color);
            }

            .seeker-admin-stat {
                height: 100%;
                padding: 1.15rem;
                border: 1px solid rgba(var(--bs-primary-rgb), .1);
                border-radius: var(--seeker-admin-radius);
                background: var(--bs-body-bg);
                box-shadow: 0 .25rem 1rem rgba(0, 0, 0, .035);
            }

            .seeker-admin-stat-icon {
                width: 2.65rem;
                height: 2.65rem;
                border-radius: .75rem;
                font-size: 1.15rem;
            }

            .seeker-admin-stat-value {
                font-size: 1.5rem;
                font-weight: 700;
                line-height: 1.2;
            }

            .seeker-admin-report-type-icon {
                display: inline-flex;
                width: 2.15rem;
                height: 2.15rem;
                align-items: center;
                justify-content: center;
                border-radius: .65rem;
                font-size: 1rem;
            }

            .seeker-admin-switch-row {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 1.5rem;
                padding: 1.15rem 1.25rem;
            }

            .seeker-admin-switch-row + .seeker-admin-switch-row {
                border-top: 1px solid var(--bs-border-color);
            }

            .seeker-admin-switch-row .form-switch {
                flex: 0 0 auto;
                margin-bottom: 0;
                padding-left: 3rem;
                font-size: 1.25rem;
            }

            .seeker-admin-detail-item {
                height: 100%;
                padding: 1rem;
                border: 1px solid var(--bs-border-color);
                border-radius: .75rem;
                background: rgba(var(--bs-secondary-rgb), .025);
            }

            .seeker-admin-choice {
                position: relative;
            }

            .seeker-admin-choice label {
                display: flex;
                min-height: 100%;
                align-items: flex-start;
                gap: .7rem;
                padding: .9rem;
                border: 1px solid var(--bs-border-color);
                border-radius: .75rem;
                background: var(--bs-body-bg);
                cursor: pointer;
                transition: border-color .18s ease, background-color .18s ease, box-shadow .18s ease, transform .18s ease;
            }

            .seeker-admin-choice label:hover {
                border-color: rgba(var(--bs-primary-rgb), .45);
                transform: translateY(-1px);
            }

            .seeker-admin-choice input:checked + label {
                border-color: var(--bs-primary);
                background: var(--seeker-admin-soft-primary);
                box-shadow: 0 0 0 .2rem rgba(var(--bs-primary-rgb), .08);
            }

            .seeker-admin-choice input:focus-visible + label {
                outline: 3px solid rgba(var(--bs-primary-rgb), .25);
                outline-offset: 2px;
            }

            .seeker-admin-user {
                display: flex;
                align-items: center;
                gap: .7rem;
            }

            .seeker-admin-user img {
                width: 2.35rem;
                height: 2.35rem;
                object-fit: cover;
            }

            .seeker-admin-user-combobox {
                position: relative;
            }

            .seeker-admin-user-combobox-results {
                position: absolute;
                top: calc(100% - 1.15rem);
                right: 0;
                left: 0;
                z-index: 1080;
                max-height: 16rem;
                overflow-y: auto;
                border-radius: .65rem;
                box-shadow: 0 .75rem 2rem rgba(0, 0, 0, .14);
            }

            .seeker-admin-user-option {
                display: flex;
                align-items: center;
                gap: .7rem;
                text-align: left;
            }

            .seeker-admin-user-option img {
                flex: 0 0 auto;
                object-fit: cover;
            }

            .seeker-admin-user-combobox-spinner {
                color: var(--bs-primary);
                background: var(--bs-body-bg);
            }

            .seeker-admin-selected-user {
                display: flex;
                min-height: calc(2.25rem + 2px);
                align-items: center;
                gap: .7rem;
                padding: .45rem .6rem;
                border: 1px solid var(--bs-border-color);
                border-radius: var(--bs-border-radius);
                background: var(--bs-body-bg);
            }

            .seeker-admin-selected-user img {
                flex: 0 0 auto;
                object-fit: cover;
            }

            .seeker-admin-selected-user .min-w-0 {
                min-width: 0;
            }

            .seeker-admin-status {
                display: inline-flex;
                align-items: center;
                gap: .3rem;
                border-radius: 999px;
                font-size: .74rem;
                font-weight: 700;
            }

            .seeker-admin-message {
                max-width: min(42rem, 90%);
                padding: 1rem;
                border: 1px solid var(--bs-border-color);
                border-radius: .85rem;
                background: var(--bs-body-bg);
                box-shadow: 0 .15rem .6rem rgba(0, 0, 0, .025);
            }

            .seeker-admin-message.is-client {
                border-color: rgba(var(--bs-primary-rgb), .18);
                background: rgba(var(--bs-primary-rgb), .045);
            }

            @media (max-width: 767.98px) {
                .seeker-admin-header {
                    align-items: flex-start;
                    flex-direction: column;
                }

                .seeker-admin-header-actions,
                .seeker-admin-header-actions > * {
                    width: 100%;
                }

                .seeker-admin-heading {
                    align-items: flex-start;
                }

                .seeker-admin-icon {
                    width: 2.65rem;
                    height: 2.65rem;
                    font-size: 1.15rem;
                }

                .seeker-admin-total {
                    margin-left: 3.55rem;
                }

                .seeker-admin-toolbar {
                    padding: .9rem;
                }

                .seeker-admin-filter-actions,
                .seeker-admin-filter-actions > * {
                    width: 100%;
                }

                .seeker-admin-filter-submit {
                    padding-top: 0;
                }

                .seeker-admin-switch-row {
                    align-items: center;
                    gap: 1rem;
                    padding: 1rem;
                }

                .seeker-admin-card > .card-body.p-4 {
                    padding: 1rem !important;
                }

                .seeker-admin-pagination {
                    padding: .85rem 1rem;
                }
            }

            @media (prefers-reduced-motion: reduce) {
                .seeker-admin-choice label {
                    transition: none;
                }

                .seeker-admin-choice label:hover {
                    transform: none;
                }
            }
        </style>
    @endpush
@endonce
