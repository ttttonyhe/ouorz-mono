( function ( $, root, undefined ) {
    root.rediscache = root.rediscache || {};
    var rediscache = root.rediscache;

    $.extend( rediscache, {
        metrics: {
            computed: null,
        },
        chart: null,
        chart_defaults: {
            noData: {
                text: root.rediscache_metrics
                    ? rediscache.l10n.no_data
                    : rediscache.l10n.no_cache,
                align: 'center',
                verticalAlign: 'middle',
                offsetY: -25,
                style: {
                    color: '#72777c',
                    fontSize: '14px',
                    fontFamily: 'inherit',
                }
            },
            stroke: {
                width: [2, 2],
                curve: 'smooth',
                dashArray: [0, 8],
            },
            colors: [
                '#0096dd',
                '#72777c',
            ],
            annotations: {
                texts: [{
                    x: '15%',
                    y: '30%',
                    fontSize: '20px',
                    fontWeight: 600,
                    fontFamily: 'inherit',
                    foreColor: '#72777c',
                }],
            },
            chart: {
                type: 'line',
                height: $( '#metrics-pane #widget-redis-stats' ).length ? '300px' : '100%',
                toolbar: { show: false },
                zoom: { enabled: false },
                animations: { enabled: false },
            },
            dataLabels: {
                enabled: false,
            },
            legend: {
                show: false,
            },
            fill: {
                opacity: [0.25, 1],
            },
            xaxis: {
                type: 'datetime',
                labels: {
                    format: 'HH:mm',
                    datetimeUTC: false,
                    style: { colors: '#72777c', fontSize: '13px', fontFamily: 'inherit' },
                },
                tooltip: { enabled: false },
            },
            yaxis: {
                type: 'numeric',
                tickAmount: 4,
                min: 0,
                labels: {
                    style: { colors: '#72777c', fontSize: '13px', fontFamily: 'inherit' },
                    formatter: function ( value ) {
                        return Math.round( value );
                    },
                },
            },
            tooltip: {
                fixed: {
                    enabled: true,
                    position: 'bottomLeft',
                    offsetY: 15,
                    offsetX: 0,
                },
            }
        },
        templates: {
            tooltip_title: _.template(
                '<div class="apexcharts-tooltip-title"><%- title %></div>'
            ),
            series_group: _.template(
                '<div class="apexcharts-tooltip-series-group">' +
                '  <span class="apexcharts-tooltip-marker" style="background-color: <%- color %>;"></span>' +
                '  <div class="apexcharts-tooltip-text">' +
                '    <div class="apexcharts-tooltip-y-group">' +
                '      <span class="apexcharts-tooltip-text-label"><%- name %>:</span>' +
                '      <span class="apexcharts-tooltip-text-value"><%- value %></span>' +
                '    </div>' +
                '  </div>' +
                '</div>'
            ),
            series_pro: _.template(
                '<div class="apexcharts-tooltip-series-group">' +
                '  <span class="apexcharts-tooltip-marker" style="background-color: <%- color %>;"></span>' +
                '  <div class="apexcharts-tooltip-text">' +
                '    <div class="apexcharts-tooltip-y-group">' +
                '      <span class="apexcharts-tooltip-text-label"><%- name %></span>' +
                '    </div>' +
                '  </div>' +
                '</div>'
            ),
        }
    } );

    // Build the charts by deep extending the chart defaults
    $.extend( rediscache, {
        charts: {
            time: $.extend( true, {}, rediscache.chart_defaults, {
                yaxis: {
                    labels: {
                        formatter: function ( value ) {
                            return Math.round( value ) + ' ms';
                        },
                    },
                },
                tooltip: {
                    custom: function ({ series, seriesIndex, dataPointIndex, w }) {
                        return [
                            rediscache.templates.tooltip_title({
                                title: new Date( w.globals.seriesX[ seriesIndex ][ dataPointIndex ] )
                                    .toTimeString().slice( 0, 5 ),
                            }),
                            rediscache.templates.series_group({
                                color: rediscache.chart_defaults.colors[0],
                                name: w.globals.seriesNames[0],
                                value: series[0][ dataPointIndex ].toFixed(2) + ' ms',
                            }),
                            rediscache.templates.series_pro({
                                color: rediscache.chart_defaults.colors[1],
                                name: rediscache.l10n.pro,
                            }),
                        ].join('');
                    },
                },
            } ),
            bytes: $.extend( true, {}, rediscache.chart_defaults, {
                yaxis: {
                    labels: {
                        formatter: function ( value ) {
                            var i = value === 0 ? 0 : Math.floor( Math.log( value ) / Math.log( 1024 ) );

                            return parseFloat( (value / Math.pow( 1024, i ) ).toFixed( i ? 2 : 0 ) ) + ' ' + ['B', 'KB', 'MB', 'GB', 'TB'][i];
                        },
                    },
                },
                tooltip: {
                    custom: function ({ series, seriesIndex, dataPointIndex, w }) {
                        var value = series[0][ dataPointIndex ];
                        var i = value === 0 ? 0 : Math.floor( Math.log( value ) / Math.log( 1024 ) );
                        var bytes = parseFloat( (value / Math.pow( 1024, i ) ).toFixed( i ? 2 : 0 ) ) + ' ' + ['B', 'KB', 'MB', 'GB', 'TB'][i];

                        return [
                            rediscache.templates.tooltip_title({
                                title: new Date( w.globals.seriesX[ seriesIndex ][ dataPointIndex ] ).toTimeString().slice( 0, 5 ),
                            }),
                            rediscache.templates.series_group({
                                color: rediscache.chart_defaults.colors[0],
                                name: w.globals.seriesNames[0],
                                value: bytes,
                            }),
                            rediscache.templates.series_pro({
                                color: rediscache.chart_defaults.colors[1],
                                name: rediscache.l10n.pro,
                            }),
                        ].join('');
                    },
                },
            } ),
            ratio: $.extend( true, {}, rediscache.chart_defaults, {
                yaxis: {
                    max: 100,
                    labels: {
                        formatter: function ( value ) {
                            return Math.round( value ) + '%';
                        },
                    },
                },
                tooltip: {
                    custom: function ({ series, seriesIndex, dataPointIndex, w }) {
                        return [
                            rediscache.templates.tooltip_title({
                                title: new Date( w.globals.seriesX[ seriesIndex ][ dataPointIndex ] )
                                    .toTimeString().slice( 0, 5 ),
                            }),
                            rediscache.templates.series_group({
                                color: rediscache.chart_defaults.colors[0],
                                name: w.globals.seriesNames[0],
                                value: Math.round( series[0][ dataPointIndex ] * 100 ) / 100 + '%',
                            }),
                        ].join('');
                    },
                },
            } ),
            calls: $.extend( true, {}, rediscache.chart_defaults, {
                tooltip: {
                    custom: function ({ series, seriesIndex, dataPointIndex, w }) {
                        return [
                            rediscache.templates.tooltip_title({
                                title: new Date( w.globals.seriesX[ seriesIndex ][ dataPointIndex ] )
                                    .toTimeString().slice( 0, 5 ),
                            }),
                            rediscache.templates.series_group({
                                color: rediscache.chart_defaults.colors[0],
                                name: w.globals.seriesNames[0],
                                value: Math.round( series[0][ dataPointIndex ] ),
                            }),
                            rediscache.templates.series_pro({
                                color: rediscache.chart_defaults.colors[1],
                                name: rediscache.l10n.pro,
                            }),
                        ].join('');
                    },
                },
            } ),
        },
    } );

    var compute_metrics = function ( raw_metrics ) {
        var metrics = {};

        // parse raw metrics in blocks of minutes
        for ( var entry in raw_metrics ) {
            var values = {};
            var timestamp = raw_metrics[ entry ].timestamp;
            var minute = ( timestamp - timestamp % 60 ) * 1000;

            for ( var key in raw_metrics[ entry ] ) {
                if ( raw_metrics[ entry ].hasOwnProperty( key ) ) {
                    values[ key ] = Number( raw_metrics[ entry ][ key ] );
                }
            }

            if ( ! metrics[ minute ] ) {
                metrics[ minute ] = [];
            }

            metrics[ minute ].push( values );
        }

        // calculate median value for each block
        for ( var entry in metrics ) {
            if ( metrics[ entry ].length === 1 ) {
                metrics[ entry ] = metrics[ entry ].shift();
                continue;
            }

            var medians = {};

            for ( var key in metrics[ entry ][0] ) {
                medians[ key ] = compute_median(
                    metrics[ entry ].map(
                        function ( metric ) {
                            return metric[ key ];
                        }
                    )
                );
            }

            metrics[ entry ] = medians;
        }

        var computed = [];

        for ( var timestamp in metrics ) {
            var entry = metrics[ timestamp ];

            entry.date = Number( timestamp );
            entry.time = entry.time * 1000;

            computed.push( entry );
        }

        computed.sort(
            function( a, b ) {
                return a.date - b.date;
            }
        );

        return computed.length < 2 ? [] : computed;
    };

    var compute_median = function ( numbers ) {
        var median = 0;
        var numsLen = numbers.length;

        numbers.sort();

        if ( numsLen % 2 === 0 ) {
            median = ( numbers[ numsLen / 2 - 1 ] + numbers[ numsLen / 2 ] ) / 2;
        } else {
            median = numbers[ ( numsLen - 1 ) / 2 ];
        }

        return median;
    };

    var render_chart = function ( id ) {
        if ( rediscache.chart ) {
            rediscache.chart.updateOptions( rediscache.charts[ id ] );
            return;
        }

        var chart = new ApexCharts(
            document.querySelector( '#redis-stats-chart' ),
            rediscache.charts[ id ]
        );

        chart.render();
        root.rediscache.chart = chart;
    };

    var setup_charts = function () {
        var metrics = {};

        for ( var type in rediscache.charts ) {
            if ( ! rediscache.charts.hasOwnProperty( type ) ) {
                continue;
            }

            metrics[type] = rediscache.metrics.computed.map(
                function ( entry ) {
                    return [ entry.date, entry[type] ];
                }
            );

            rediscache.charts[type].series = [{
                name: rediscache.l10n[type],
                type: 'area',
                data: metrics[type],
            }];
        }

        if ( ! rediscache.disable_pro || ! rediscache.disable_banners ) {
            var pro_charts = {
                time: function ( entry ) {
                    return [ entry[0], entry[1] * 0.5 ]
                },
                bytes: function ( entry ) {
                    return [ entry[0], entry[1] * 0.3 ]
                },
                calls: function ( entry ) {
                    return [ entry[0], Math.round( entry[1] / 50 ) + 5 ]
                },
            };

            for ( var type in pro_charts ) {
                if ( ! rediscache.charts[type] ) {
                    continue;
                }

                rediscache.charts[type].series.push({
                    name: rediscache.l10n.pro,
                    type: 'line',
                    data: metrics[type].map( pro_charts[type] ),
                });
            }

        }
    };

    // executed on page load
    $(function () {
        var $tabs = $( '#rediscache .nav-tab-wrapper' );
        var $panes = $( '#rediscache .content-column .tab-content' );

        $tabs.find( 'a' ).on(
            'click.redis-cache',
            function ( event ) {
                var toggle = $( this ).data( 'toggle' );

                $( this ).blur();

                show_tab( toggle );

                if ( history.pushState ) {
                    history.pushState( null, null, '#' + toggle );
                }

                return false;
            }
        );

        var firstRender = window.location.hash.indexOf('metrics') === -1;

        var show_tab = function ( name ) {
            $tabs.find( '.nav-tab-active' ).removeClass( 'nav-tab-active' );
            $panes.find( '.tab-pane.active' ).removeClass( 'active' );

            $( '#' + name + '-tab' ).addClass( 'nav-tab-active' );
            $( '#' + name + '-pane' ).addClass( 'active' );

            if (name === 'metrics' && firstRender) {
                firstRender = false;
                render_chart( 'time' );
            }
        };

        var show_current_tab = function () {
            var tabHash = window.location.hash.replace( '#', '' );

            if ( tabHash !== '' && $( '#' + tabHash + '-tab' ) ) {
                show_tab( tabHash );
            }
        };

        show_current_tab();

        $( window ).on( 'hashchange', show_current_tab );

        if ( $( '#widget-redis-stats' ).length ) {
            rediscache.metrics.computed = compute_metrics( root.rediscache_metrics );

            setup_charts();
            render_chart( 'time' );
        }

        $( '#widget-redis-stats ul a[data-chart]' ).on(
            'click.redis-cache',
            function ( event ) {
                event.preventDefault();

                $( '#widget-redis-stats .active' ).removeClass( 'active' );
                $( this ).blur().addClass( 'active' );

                render_chart(
                    $( event.target ).data( 'chart' )
                );
            }
        );

        $( '.notice.is-dismissible[data-dismissible]' ).on(
            'click.roc-dismiss-notice',
            '.notice-dismiss',
            function ( event ) {
                event.preventDefault();

                var $parent = $( this ).parent();

                $.post( ajaxurl, {
                    notice: $parent.data( 'dismissible' ),
                    action: 'roc_dismiss_notice',
                    _ajax_nonce: $parent.data( 'nonce' ),
                } );
            }
        );

        if ( $( '#redis-cache-copy-button' ).length ) {
            if ( typeof ClipboardJS === 'undefined' ) {
                $( '#redis-cache-copy-button' ).remove();
            } else {
                var successTimeout;
                var clipboard = new ClipboardJS( '#redis-cache-copy-button .copy-button' );

                clipboard.on( 'success', function( e ) {
                    var triggerElement = $( e.trigger ),
                        successElement = $( '.success', triggerElement.closest( 'div' ) );

                    e.clearSelection();
                    triggerElement.trigger( 'focus' );

                    clearTimeout( successTimeout );
                    successElement.removeClass( 'hidden' );

                    successTimeout = setTimeout( function() {
                        successElement.addClass( 'hidden' );

                        if ( clipboard.clipboardAction.fakeElem && clipboard.clipboardAction.removeFake ) {
                            clipboard.clipboardAction.removeFake();
                        }
                    }, 3000 );

                } );
            }
        }
    });

} ( window[ rediscache.jQuery ], window ) );
