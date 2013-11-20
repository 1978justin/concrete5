/**
 * Base search class for AJAX searching
 */

!function(window, $) {
	'use strict';

	function ConcreteAjaxSearch($element, options) {
		options = options || {};
		options = $.extend({
			'result': {},
			'onLoad': false
		}, options);
		this.$element = $element;
		this.$results = $element.find('div[data-search-element=results]');
		this.$resultsTableBody = this.$results.find('tbody');
		this.$resultsTableHead = this.$results.find('thead');
		this.$resultsPagination = this.$results.find('div.ccm-search-results-pagination');
		this.$menuTemplate = $element.find('script[data-template=search-results-menu]');
		this.$searchFieldRowTemplate = $element.find('script[data-template=search-field-row]');

		this.options = options;

		this._templateSearchForm = _.template($element.find('script[data-template=search-form]').html());
		this._templateSearchResultsTableHead = _.template($element.find('script[data-template=search-results-table-head]').html());
		this._templateSearchResultsTableBody = _.template($element.find('script[data-template=search-results-table-body]').html());
		this._templateSearchResultsPagination = _.template($element.find('script[data-template=search-results-pagination]').html());
		if (this.$menuTemplate.length) {
			this._templateSearchResultsMenu = _.template(this.$menuTemplate.html());
		}
		if (this.$searchFieldRowTemplate.length) {
			this._templateAdvancedSearchFieldRow = _.template(this.$searchFieldRowTemplate.html());
		}

		this.setupSearch();
		this.setupCheckboxes();
		this.setupBulkActions();
		this.setupSort();
		this.setupPagination();
		this.setupAdvancedSearch();
		this.setupCustomizeColumns();
		this.updateResults(options.result);

		if (options.onLoad) {
			options.onLoad(this);
		}
	}

	ConcreteAjaxSearch.prototype.ajaxUpdate = function(url, data, callback) {
		data = data || [];
		var cs = this;
		jQuery.fn.dialog.showLoader();
		$.ajax({
			type: 'post', 
			data: data,
			dataType: 'json',
			url: url,
			complete: function() {
				jQuery.fn.dialog.hideLoader();
			},
			error: function(r) {
				ConcreteAlert.notice(r);
			},
			success: function(r) {
				if (!callback) {
					cs.updateResults(r);
				} else {
					callback(r);
				}
			}
		});
	}

	ConcreteAjaxSearch.prototype.setupMenus = function() {
		var cs = this;
		if (cs.$menuTemplate.length) {
			cs.$element.find('[data-search-menu]').remove();
			// loop through all results,
			// create nodes for them.
			$.each(cs.options.result.items, function(i, item) {
				cs.$results.append(cs._templateSearchResultsMenu({'item': item}));
			});
			cs.$element.find('tbody tr').each(function() {
				$(this).concreteMenu({
					'menu': $('[data-search-menu=' + $(this).attr('data-launch-search-menu') + ']')
				});
			});
		}
	}

	ConcreteAjaxSearch.prototype.setupCustomizeColumns = function() {
		var cs = this;
		cs.$element.on('click', 'a[data-search-toggle=customize]', function() {
			var url = $(this).attr('data-search-column-customize-url');
			$.fn.dialog.open({
				width: 480,
				height: 400,
				href: url,
				modal: true,
				title: ccmi18n.customizeSearch,
				onOpen: function() {
					var $form = $('form[data-dialog-form=search-customize'),
						$selectDefault = $form.find('select[data-search-select-default-column]'),
						$columns = $form.find('ul[data-search-column-list]');

					$('ul[data-search-column-list]').sortable({
						cursor: 'move',
						opacity: 0.5
					});
					$form.on('click', 'input[type=checkbox]', function() {
						var label = $(this).parent().find('span').html(),
							id = $(this).attr('id');
						
						if ($(this).prop('checked')) {
							if ($form.find('li[data-field-order-column=' + id + ']').length == 0) {
								$selectDefault.append($('<option>', {'value': id, 'text': label}));
								$selectDefault.prop('disabled', false);
								$columns.append('<li data-field-order-column="' + id + '"><input type="hidden" name="column[]" value="' + id + '" />' + label + '<\/li>');
							}
						} else {
							$columns.find('li[data-field-order-column=' + id + ']').remove();
							$selectDefault.find('option[value=' + id + ']').remove();
							if ($columns.find('li').length == 0) {
								$selectDefault.prop('disabled', true);
							}
						}
					});
					ccm_event.subscribe('AjaxFormSubmitSuccess', function(e) {
						cs.updateResults(e.eventData.result);
					}, $form.get(0));
				}
			});
			return false;
		});
	}

	ConcreteAjaxSearch.prototype.updateResults = function(result) {
		var cs = this;
		cs.$resultsTableHead.html(cs._templateSearchResultsTableHead({'columns': result.columns}));
		cs.$resultsTableBody.html(cs._templateSearchResultsTableBody({'items': result.items}));
		cs.$resultsPagination.html(cs._templateSearchResultsPagination({'pagination': result.pagination}));

		cs.$advancedFields.html('');
		$.each(result.fields, function(i, field) {
			cs.$advancedFields.append(cs._templateAdvancedSearchFieldRow({'field': field}));
		});
		cs.setupMenus();
	}

	ConcreteAjaxSearch.prototype.setupAdvancedSearch = function() {
		var cs = this;
		cs.$advancedFields = cs.$element.find('div.ccm-search-fields-advanced');

		cs.$element.on('click', 'a[data-search-toggle=advanced]', function() {
			cs.$advancedFields.append(cs._templateAdvancedSearchFieldRow());
			return false;
		});
		cs.$element.on('change', 'select[data-search-field]', function() {
			var $content = $(this).parent().find('.ccm-search-field-content');
			$content.html('');
			var field = $(this).find(':selected').attr('data-search-field-url');
			if (field) {
				cs.ajaxUpdate(field, false, function(r) {
					$content.html(r.html);
				});
			}
		});
		cs.$element.on('click', 'a[data-search-remove=search-field]', function() {
			var $row = $(this).parent();
			$row.queue(function () {
				$(this).addClass('ccm-search-field-removing');
				$(this).dequeue()
			}).delay(200).queue(function() {
				$(this).remove();
				$(this).dequeue();
			});
			return false;
		});
	}

	ConcreteAjaxSearch.prototype.setupSort = function() {
		var cs = this;
		this.$element.on('click', 'thead th a', function() {
			cs.ajaxUpdate($(this).attr('href'));
			return false;
		});
	}

	ConcreteAjaxSearch.prototype.setupSearch = function() {
		var cs = this;
		cs.$element.find('[data-search-element=wrapper]').html(cs._templateSearchForm());
		cs.$element.on('submit', 'form[data-search-form]', function() {
			var data = $(this).serializeArray();
			data.push({'name': 'submitSearch', 'value': '1'});
			cs.ajaxUpdate($(this).attr('action'), data);
			return false;
		});
	}

	ConcreteAjaxSearch.prototype.setupBulkActions = function() {
		var cs = this;
		cs.$bulkActions = cs.$element.find('select[data-bulk-action]');
		cs.$element.on('change', 'select[data-bulk-action]', function() {
			var $option = $(this).find('option:selected'),
				value = $option.attr('data-bulk-action-type'),
				items = [];

			$.each(cs.$element.find('input[data-search-checkbox=individual]:checked'), function(i, checkbox) {
				items.push({'name': 'item[]', 'value': $(checkbox).val()});
			});
			if (value == 'dialog') {
				jQuery.fn.dialog.open({
					width: $option.attr('data-bulk-action-dialog-width'),
					height: $option.attr('data-bulk-action-dialog-height'),
					modal: true,
					href: $option.attr('data-bulk-action-url') + '?' + jQuery.param(items),
					title: $option.attr('data-bulk-action-title')				
				});
			}
		});
	}

	ConcreteAjaxSearch.prototype.setupPagination = function() {
		var cs = this;
		this.$element.on('click', 'ul.pagination a', function() {
			cs.ajaxUpdate($(this).attr('href'));
			return false;
		});
	}

	ConcreteAjaxSearch.prototype.setupCheckboxes = function() {
		var cs = this;
		cs.$element.on('click', 'input[data-search-checkbox=select-all]', function() {
			cs.$element.find('input[data-search-checkbox=individual]').prop('checked', $(this).is(':checked')).trigger('change');
		});
		cs.$element.on('change', 'input[data-search-checkbox=individual]', function() {
			if (cs.$element.find('input[data-search-checkbox=individual]:checked').length) {
				cs.$bulkActions.prop('disabled', false);
			} else {
				cs.$bulkActions.prop('disabled', true);
			}
		});

	}

	// jQuery Plugin
	$.fn.concreteAjaxSearch = function(options) {
		return new ConcreteAjaxSearch(this, options);
	}

}(window, $);