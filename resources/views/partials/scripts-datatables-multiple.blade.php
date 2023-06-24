<!-- DataTables  & Plugins -->
<script src="{{ admin_asset_url('plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ admin_asset_url('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ admin_asset_url('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ admin_asset_url('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
<script src="{{ admin_asset_url('plugins/datatables-buttons/js/dataTables.buttons.min.js') }}"></script>
<script src="{{ admin_asset_url('plugins/datatables-buttons/js/buttons.bootstrap4.min.js') }}"></script>
<script src="{{ admin_asset_url('plugins/jszip/jszip.min.js') }}"></script>
<script src="{{ admin_asset_url('plugins/pdfmake/pdfmake.min.js') }}"></script>
<script src="{{ admin_asset_url('plugins/pdfmake/vfs_fonts.js') }}"></script>
<script src="{{ admin_asset_url('plugins/datatables-buttons/js/buttons.html5.min.js') }}"></script>
<script src="{{ admin_asset_url('plugins/datatables-buttons/js/buttons.print.min.js') }}"></script>
<script src="{{ admin_asset_url('plugins/datatables-buttons/js/buttons.colVis.min.js') }}"></script>
<!-- Page specific script -->
<script>

	var table;

	var listView = {
		tableSelector: '.datatable',
		filterFormSelector: '#filter-form',
		dtOptions: {},
		filterQuery: null,
	}

	$(document).ready(function () {
        $(listView.tableSelector).each(function(key, val) {
            const id = `#${val.id}`;
            const options = setDataTableOptions(id);
            drawColumns(id, options);
            initDataTables(id, options);
            // initExtraFilters(id);
        });

        initModal(response => {
            c(response);
            refreshTable();
        });
	});

	function setDataTableOptions(id) {
		c("setDataTableOptions");
		let dtOptions = $(id).data('options');
		let ajaxData = {};
		
		if(ok(dtOptions.ajax)) {
			dtOptions.ajax['data'] = dtOptions.ajax.data ?? ajaxData;
		}

		dtOptions.pagingType = 'simple';

        return dtOptions;
	}

	function drawColumns(id, options) {
		let thead = $(id).find('thead');
		thead.append($('<tr/>'));
		let tr = thead.find('tr');
		for(index in options.columns) {
			const item = options.columns[index];
			let th = $('<th/>').text(item.title);
			tr.append(th);
		}
	}

	function initDataTables(id, options) {

		// cc('DataTable Options', listView.dtOptions);

		$.fn.dataTable.ext.errMode = 'none';

		$.fn.dataTable.ext.buttons.reload = {
			action: function ( e, dt, node, config ) {
				refreshTable();
			}
		};

		$.fn.dataTable.ext.buttons.search = {
			action: function ( e, dt, node, config ) {
				toggleFilterPanel()
			}
		};

		$.fn.dataTable.ext.buttons.action = {
			action: function ( e, dt, node, config ) {
				navigate($(e.currentTarget).data('url'), 0)
			}
		};

		/* $.fn.dataTable.ext.buttons.bulk = {
			action: function ( e, dt, node, config ) {
				if($('.select-row:checked').length < 1) {
					swal("No selections", "You need to select atleast one row item to use this feature", "error");
					return;
				}
				swalConfirm(`Do you want to perform '${$(e.currentTarget).attr('title')}' operation on these ${$('.select-row:checked').length} items?`, function() {
					const data = $('#datatable-form').serializeArray().reduce(function (obj, item) {
						if (obj[item.name] == null) {
							obj[item.name] = [];
						} 
						obj[item.name].push(item.value);
						return obj;
					}, {});

					$.ajax({
						type: 'POST',
						url: $(e.currentTarget).data('url'),
						data: data, 
						dataType: 'json', 
						success: function(response) {
							if(ok(response.message)) toastSuccess(response.message);
							refreshTable();
							$('#select-all').prop('checked', false);
						},
						error: function (jqXHR) {
							let response = jqXHR.responseJSON;
							if(ok(response.message) && response.message) toastError(response.message);
							else toastError(`Your request failed. Server responded with code: ${jqXHR.status}`);
						}
					});
				});
			}
		}; */

		table = $(id).DataTable(options);
        // table.buttons().container().appendTo('#datatable_wrapper .col-md-12:eq(0)');
		table.on('error.dt', function(e, settings, techNote, message) {
			c(message);
        });

		$('#select-all').on('click', function() {
			$('input.select-row').prop('checked', this.checked);
		});
	}

	function refreshTable() {
		if (table instanceof $.fn.dataTable.Api) {
			table.ajax.reload();
			c("DataTable reloaded");
		}
		else {
			c("Cannot refresh: DataTable not initiated");
		}
		return false;
	}

	function resetTable() {
		if (table instanceof $.fn.dataTable.Api) {
			table.state.clear();
			c("DataTable state Cleared");
			location.reload();
		}
		else {
			c("Cannot reset: DataTable not initiated");
		}
		return false;
	}

	function reinitTable() {
		if (table instanceof $.fn.dataTable.Api) {
			table.destroy();
			c("DataTable Destroyed");
			initDataTables();
		}
		else {
			c("Cannot reinit: DataTable not initiated");
		}
		return false;
	}

	function toggleFilterPanel() {
		$('.collapse').toggle()
	}

	function initExtraFilters() {

		loadFilterCache();
		hasFilterData = false;

		$('.extra-filter').each(function(i, input){
			let formInput = $(input);
			formInput.keypress(function (e) {
				var key = e.which;
				if(key == 13)  // the enter key code
				{
					setExtraFilters();
					return false;  
				}
			});
			if(!hasFilterData && formInput.val()) hasFilterData = true;
		});

		if(hasFilterData) {
			setExtraFilters();
			toggleFilterPanel();
		}
	}

	function clearExtraFilters() {
		$('.extra-filter').each(function(i, input){
			let formInput = $(input);
			formInput.val('').trigger('change');
		});
		setExtraFilters();
		clearFilterCache();
	}

	function setExtraFilters() {
		filterQuery = $(listView.filterFormSelector).serializeArray().filter(function (i) {
			return i.value;
		});
		listView.filterQuery = $.param(filterQuery);
		filterTable();
		saveFilterCache();
	}

	function filterTable() {
		if(listView.filterQuery) {
			let url = listView.dtOptions.ajax.url + '?' + listView.filterQuery
			table.ajax.url(url).load();
		}
		else {
			table.ajax.url(listView.dtOptions.ajax.url).load();
		}
	}

	function loadFilterCache() {
		if(ok(localStorage.getItem("filterData_" + window.location.pathname))) {
			let formArray = JSON.parse(localStorage.getItem("filterData_" + window.location.pathname));
			cc("cachedFilterData", formArray);
			formArray.forEach(function (pair) {
				var selector = `[name="${ pair.name }"]`
				var input = $(listView.filterFormSelector).find(selector)
				input.val(pair.value).trigger('change');
			});
		}
	}

	function saveFilterCache() {
		localStorage.setItem("filterData_" + window.location.pathname, JSON.stringify($(listView.filterFormSelector).serializeArray()));
	}

	function clearFilterCache() {
		localStorage.removeItem('filterData_' + window.location.pathname);
	}

</script>