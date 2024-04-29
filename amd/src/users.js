define([
    "jquery",
    "local_datatables/repository",
    "local_datatables/datatables/dataTables.helper",
    "core/notification",
    "local_datatables/datatables/dataTables.dateTime",
    "local_datatables/datatables/moment",
    "local_datatables/datatables/i18n/en-GB",
    "local_datatables/datatables/i18n/el-GR",
    "local_datatables/datatables/jquery.dataTables",
    "local_datatables/datatables/dataTables.bootstrap4",
    "local_datatables/datatables/dataTables.responsive",
    "local_datatables/datatables/responsive.bootstrap4",
    "local_datatables/datatables/dataTables.buttons",
    "local_datatables/datatables/buttons.bootstrap4",
    "local_datatables/datatables/buttons.colVis",
    "local_datatables/datatables/buttons.html5",
    "local_datatables/datatables/buttons.print",
    "local_datatables/bootstrap-select/bootstrap-select",
], function($,
    Repository,
    Helper,
    Notification,
    DateTime,
    moment,
    languageENGB,
    languageELGR,
    ) {
    var init = function() {
        $(function() {
            var tableid = '#users';
            var orderColumn = 1; // Set the default ordering column index

            var table = $(tableid).DataTable({
                initComplete: function() {
                    // Add key type events with 1 second of delay for searching
                    var typingTimer;
                    var doneTypingInterval = 500; // 0.5 second

                    $(tableid + " .filters").on("keyup", "input", function() {
                        // Clear the existing timer if it exists
                        clearTimeout(typingTimer);

                        // Start a new timer
                        typingTimer = setTimeout(function() {
                            table.column($(this).data("index")).search(this.value).draw();
                        }.bind(this), doneTypingInterval);
                    });

                    // Event for handling when the user stops typing
                    $(tableid + " .filters").on("keydown", "input", function() {
                        // Clear the existing timer if it exists
                        clearTimeout(typingTimer);
                    });

                    // Trigger search on select lists
                    $(tableid + " .filters").on("change", "select", function() {
                        // Apply the filter for selected values
                        var selectedValues = $(this).val();

                        if (Array.isArray(selectedValues) && selectedValues.length > 1) {
                            table.column($(this).data("index")).search(selectedValues.join('|'), true, false).draw();
                        } else {
                            table.column($(this).data("index")).search(selectedValues).draw();
                        }
                    });

                    // Init datetime fields
                    const datetimeElements = document.querySelectorAll('.datetime');

                    if (datetimeElements) {
                        datetimeElements.forEach(function(element) {
                            const index = element.getAttribute('data-index');
                            DateTime.use(moment);
                            new DateTime(element, {
                                format: 'DD/MM/YY',
                                buttons: {
                                    clear: true
                                },
                                onChange: function(value, date, input) {
                                    table.column(index).search(value).draw();
                                }
                            });
                        });
                    }

                    // Show the datatable.
                    $(tableid).removeClass('hidden');

                    // Init selectpicker.
                    $('.selectpicker').selectpicker('render');

                    Helper.initResponsiveTable(table);
                },
                processing: true,
                serverSide: true,
                ajax: function(data, callback) {
                    Repository.process({
                        'data': JSON.stringify(data),
                        'namespace': 'local_registration',
                        'tableid': 'users',
                    })
                    .then(function(json) {
                        callback(JSON.parse(json));
                        return;
                    })
                    .catch(function(error) {
                        Notification.exception(error);
                    });
                },
                searchDelay: 400,
                orderCellsTop: true,
                fixedHeader: true,
                language: languageENGB,
                responsive: {
                    details: {
                        type: 'column',
                        target: 0,
                    }
                },
                paging: true,
                order: [[orderColumn, "asc"]],
                lengthChange: true,
                lengthMenu: [
                    [10, 20, 30, -1],
                    [10, 20, 30, "All"],
                ],
                columns: [
                    // Data: data to display,
                    // name: Raw data column name (could be an alias column name).
                    // orderable: column name for ordering.
                    // searchable: column name for searching.
                    {defaultContent: ''},
                    {data: "firstname", name: "firstname"},
                    {data: "lastname", name: "lastname"},
                    {
                        data: "email", name: "email",
                        render: function(data, type, row) {
                            if (row.notified == 1) {
                                return row.email + '<br><span class="badge bg-warning">' +
                                    M.util.get_string("notified", "local_registration") + '</span>';
                            } else {
                                return row.email;
                            }
                        }
                    },
                    {data: "tenantname", name: "t.id", orderable: "t.name", searchable: "equals||t.name"},
                    {
                        data: "country_formatted", name: "country",
                        orderable: "country_text", searchable: "equals||country_text"},
                    {data: "gender", name: "gender", searchable: "equals"},
                    {data: "domain", name: "domain", searchable: "equals"},
                    {data: "comments", name: "comments"},
                    {data: "interests_formatted", name: "interests"},
                    {
                        data: "confirmed_formatted", name: "confirmed",
                        orderable: "confirmed_text", searchable: "equals||confirmed_text"
                    },
                    {data: "timecreated_formatted", name: "lr.timecreated", searchable: "datetime"},
                    {data: "assessor_text", name: "assessor_expr", orderable: "assessor_text",
                        searchable: "assessor_expr||assessor_text"},
                    {
                        data: {},
                        render: function(data) {
                            var btnApprove = '<button class="btn btn-success btn-sm btn-approve" ' +
                            'data-action="approve" data-id="' + data.id + '">' +
                                M.util.get_string('approve', 'local_registration') + '</button>';

                            var btnReject = '<button class="btn btn-danger btn-sm btn-reject" ' +
                            'data-action="reject" data-id="' + data.id + '">' +
                                M.util.get_string('reject', 'local_registration') + '</button>';

                            var btnNotify = '';
                            if (data.notified !== 1) {
                                btnNotify = '<button class="btn btn-warning btn-sm btn-notify" ' +
                            'data-action="notify" data-id="' + data.id + '">' +
                                M.util.get_string('notify', 'local_registration') + '</button>';
                            }

                            return '<span class="buttons" data-value="' + data.id + '">' +
                                btnApprove + btnReject + btnNotify + ' </span>';
                        },
                        name: "actions",
                        orderable: false,
                        searchable: false
                    },
                ],
                columnDefs: [
                    {
                        targets: 0,
                        className: 'dtr-control noVis',
                        orderable: false,
                        searchable: false
                    },
                ],
                dom: '<B<l><t>ftrip>',
                buttons: [
                    {
                        extend: 'collection',
                        className: 'exportButton',
                        text: 'Export',
                        buttons: [
                            {
                                extend: 'copy',
                                exportOptions: {
                                    columns: ':visible:not(.noVis)',
                                }
                            },
                            {
                                extend: 'print',
                                exportOptions: {
                                    columns: ':visible:not(.noVis)',
                                }
                            },
                            {
                                extend: 'excel',
                                exportOptions: {
                                    columns: ':visible:not(.noVis)',
                                }
                            },
                            {
                                extend: 'csv',
                                exportOptions: {
                                    columns: ':visible:not(.noVis)',
                                }
                            },
                        ]
                    },
                    {
                        extend: 'colvis',
                        columns: ':not(.noVis)'
                    }
                ]
            });

            // Create modal forms on buttons click
            table.on('click', '.btn-approve',
                Helper.createModalForm(table, 'approve', 'modal:approvetitle', 'modal:approvesuccess', 'local_registration'));

            table.on('click', '.btn-reject',
                Helper.createModalForm(table, 'reject', 'modal:rejecttitle', 'modal:rejectsuccess', 'local_registration'));

            table.on('click', '.btn-notify',
                Helper.createModalForm(table, 'notify', 'modal:notifytitle', 'modal:notifysuccess', 'local_registration'));

            // Reset filters
            table.on('click', '.reset-filters', function() {
                Helper.resetTableFilters(table, false);
            });
        });
    };
    return {
        init: init,
    };
});
