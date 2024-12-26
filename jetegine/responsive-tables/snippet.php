<?php
/**
 * JetEngine Dynamic Tables Mobile Responsive Enhancement
 * 
 * This snippet transforms JetEngine's Dynamic Tables into a mobile-friendly card layout,
 * with full support for JetSmartFilters and dynamic content updates.
 * 
 * @package     JetEngine
 * @author      Stefan Radu
 * @link        https://www.wordpresstoday.agency/2024/11/12/how-to-make-jetengines-dynamic-table-responsive-for-mobile-devices/
 */

// Add responsive styles
add_action('wp_head', function() {
    ?>
    <style>
    /* Base table styles */
    .jet-dynamic-table {
        width: 100% !important;
        border-collapse: separate;
        border-spacing: 0;
    }

    /* Desktop styles */
    .jet-dynamic-table-wrapper {
        width: 100%;
        max-width: 100%;
        overflow: visible;
    }

    /* Desktop header styles */
    thead .jet-dynamic-table__row.jet-dynamic-table__row--header {
        display: table-row !important;
    }

    /* Mobile styles */
    @media screen and (max-width: 768px) {
        /* Hide desktop header */
        thead .jet-dynamic-table__row.jet-dynamic-table__row--header {
            display: none !important;
        }

        /* Container styles */
        .jet-dynamic-table-wrapper {
            display: block !important;
            overflow: visible !important;
        }

        .jet-dynamic-table {
            display: block !important;
            width: 100% !important;
        }

        .jet-dynamic-table tbody {
            display: block;
            width: 100%;
        }

        /* Row styling */
        .jet-dynamic-table__row {
            display: block !important;
            margin-bottom: 1rem;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 0.5rem;
            width: 100%;
        }

        /* Cell styling for mobile card layout */
        .jet-dynamic-table__col {
            display: grid !important;
            grid-template-columns: 50% 50% !important;
            width: 100% !important;
            min-width: 100% !important;
            padding: 0.8rem !important;
            align-items: center;
            border-bottom: 1px solid #e5e7eb !important;
        }

        .jet-dynamic-table__col:last-child {
            border-bottom: none !important;
        }

        /* Label styling */
        .jet-dynamic-table__col::before {
            content: attr(data-label);
            font-weight: 600;
            color: #4b5563;
            text-align: left;
        }

        /* Value styling */
        .jet-dynamic-table__col > * {
            text-align: right;
            margin-left: auto;
        }

        /* Handle specific cases like images */
        .jet-dynamic-table__col--logo {
            display: grid !important;
            grid-template-columns: 50% 50% !important;
        }

        .jet-dynamic-table__col--logo img {
            max-width: 100px;
            height: auto;
            margin-left: auto;
        }

        /* Remove all default table widths */
        .jet-dynamic-table__col[style*="width"],
        .jet-dynamic-table__col[style*="min-width"] {
            width: 100% !important;
            min-width: 100% !important;
        }
    }
    </style>
    <?php
});

// Add JavaScript functionality
add_action('wp_footer', function() {
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        function makeTablesResponsive() {
            const tables = document.querySelectorAll('.jet-dynamic-table');
            
            tables.forEach(table => {
                if (!table.dataset.headers) {
                    const headers = Array.from(table.querySelectorAll('thead th')).map(th => th.textContent.trim());
                    table.dataset.headers = JSON.stringify(headers);
                }
                
                const headers = JSON.parse(table.dataset.headers);
                
                const rows = table.querySelectorAll('tbody tr');
                rows.forEach(row => {
                    const cells = row.querySelectorAll('td');
                    cells.forEach((cell, index) => {
                        if (headers[index]) {
                            cell.setAttribute('data-label', headers[index]);
                        }
                    });
                });

                const wrapper = table.closest('.jet-dynamic-table-wrapper');
                if (wrapper) {
                    wrapper.style.display = 'grid';
                    if (window.innerWidth <= 480) {
                        wrapper.style.gridTemplateColumns = '1fr';
                    } else if (window.innerWidth <= 768) {
                        wrapper.style.gridTemplateColumns = 'repeat(2, 1fr)';
                    }
                }
            });
        }

        makeTablesResponsive();

        const events = [
            'jet-engine/listing/grid-load',
            'jet-filter-content-rendered',
            'jetSmartFilters/updated',
            'jetSmartFilters/updating'
        ];

        events.forEach(event => {
            document.addEventListener(event, function() {
                setTimeout(makeTablesResponsive, 100);
            });
        });

        const observer = new MutationObserver(mutations => {
            mutations.forEach(mutation => {
                if (mutation.addedNodes.length) {
                    setTimeout(makeTablesResponsive, 100);
                }
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });

        let resizeTimer;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(makeTablesResponsive, 250);
        });

        const removeFiltersButton = document.querySelector('.jet-remove-all-filters__button');
        if (removeFiltersButton) {
            removeFiltersButton.addEventListener('click', function() {
                setTimeout(makeTablesResponsive, 300);
            });
        }
    });
    </script>
    <?php
});
