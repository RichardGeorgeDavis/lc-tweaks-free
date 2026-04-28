jQuery(document).ready(function ($) {
	var dlck_ajax_saving = $('#dlck-epanel-ajax-saving');
	var dlck_ajax_loader = $('#dlck-epanel-ajax-saving').find('img');
	if (!window.dlck_admin) {
		window.dlck_admin = {nonce: ''};
	}

	function dlckStorageGetObject(key) {
		try {
			var value = window.localStorage.getItem(key);
			if (!value) {
				return {};
			}
			var parsed = JSON.parse(value);
			if (parsed && typeof parsed === 'object') {
				return parsed;
			}
		} catch (e) {}
		return {};
	}

	function dlckStorageSetObject(key, value) {
		try {
			window.localStorage.setItem(key, JSON.stringify(value));
		} catch (e) {}
	}

	function dlckSlugifyText(text) {
		return String(text || '')
			.toLowerCase()
			.replace(/[^a-z0-9]+/g, '_')
			.replace(/^_+|_+$/g, '');
	}

	function dlckParseLineList(value) {
		return String(value || '')
			.split(/\r?\n/)
			.map(function (line) { return $.trim(line); })
			.filter(function (line) { return line !== ''; });
	}

	function dlckSanitizeKey(value) {
		return String(value || '')
			.toLowerCase()
			.replace(/[^a-z0-9_-]/g, '');
	}

	function dlckNormalizePath(path) {
		var value = String(path || '/');
		value = value.split('?')[0].split('#')[0];
		if (!value) {
			value = '/';
		}
		if (value.charAt(0) !== '/') {
			value = '/' + value;
		}
		value = value.replace(/\/{2,}/g, '/');
		if (value.length > 1) {
			value = value.replace(/\/+$/g, '');
		}
		return value || '/';
	}

	function dlckUniqueStrings(values) {
		return Array.from(new Set((values || []).map(function (item) {
			return String(item || '');
		}).filter(function (item) {
			return item !== '';
		})));
	}

	function dlckInitRankMathSchemaPreviewTool() {
		var storageIgnoreKeysKey = 'dlck_rank_math_schema_preview_ignore_keys_v1';
		var $url = $('#dlck_rank_math_schema_preview_url');
		var $ignoreKeys = $('#dlck_rank_math_schema_preview_ignore_keys');
		var $chips = $('.dlck-rank-math-filter-chip');
		var $run = $('#dlck_rank_math_schema_preview_run');
		var $copyDiff = $('#dlck_rank_math_schema_preview_copy_diff');
		var $copyReport = $('#dlck_rank_math_schema_preview_copy_report');
		var $copy = $('#dlck_rank_math_schema_preview_copy');
		var $status = $('#dlck_rank_math_schema_preview_status');
		var $diff = $('#dlck_rank_math_schema_preview_diff');
		var $output = $('#dlck_rank_math_schema_preview_output');
		var previewNonce = (window.dlck_admin && window.dlck_admin.schema_preview_nonce) ? String(window.dlck_admin.schema_preview_nonce) : '';
		var lastPreviewUrl = '';
		var lastPreviewRankMathCount = 0;
		var lastPreviewDiffAvailable = false;
		var lastBasePayloads = null;
		var lastFinalPayloads = null;

		if (!$url.length || !$run.length || !$status.length || !$output.length) {
			return;
		}

		function hasElement($element) {
			return !!($element && $element.length);
		}

		function canPreviewDiff() {
			return hasElement($diff) && previewNonce !== '';
		}

		function setStatus(message, isError) {
			$status.text(String(message || '')).toggleClass('is-error', !!isError);
		}

		function setOutput(text) {
			var value = String(text || '');
			if (!value) {
				$output.text('').hide();
				if (hasElement($copy)) {
					$copy.hide();
				}
				return;
			}

			$output.text(value).show();
			if (hasElement($copy)) {
				$copy.show();
			}
		}

		function setDiff(text) {
			if (!hasElement($diff)) {
				return;
			}

			var value = String(text || '');
			if (!value) {
				$diff.text('').hide();
				if (hasElement($copyDiff)) {
					$copyDiff.hide();
				}
				if (hasElement($copyReport)) {
					$copyReport.hide();
				}
				return;
			}

			$diff.text(value).show();
			if (hasElement($copyDiff)) {
				$copyDiff.show();
			}
			if (hasElement($copyReport)) {
				$copyReport.toggle(!!lastPreviewUrl);
			}
		}

		function parseIgnoreKeys(rawValue) {
			return dlckUniqueStrings(
				String(rawValue || '')
					.split(/[,\n]/)
					.map(function (item) {
						return $.trim(String(item || '')).toLowerCase();
					})
					.filter(function (item) {
						return item !== '';
					})
			);
		}

		function renderIgnoreKeys(ignoreKeys) {
			var value = (ignoreKeys || []).join(', ');
			if (hasElement($ignoreKeys)) {
				$ignoreKeys.val(value);
			}
			if (hasElement($chips)) {
				$chips.each(function () {
					var $chip = $(this);
					var key = String($chip.data('key') || '').toLowerCase();
					var active = ignoreKeys.indexOf(key) !== -1;

					$chip.toggleClass('is-active', active).attr('aria-pressed', active ? 'true' : 'false');
				});
			}

			try {
				window.localStorage.setItem(storageIgnoreKeysKey, value);
			} catch (e) {}
		}

		function buildTargetUrl(rawValue, extraParams) {
			var value = $.trim(String(rawValue || ''));
			var targetUrl;
			var params = extraParams || {};

			if (!value) {
				return null;
			}

			try {
				targetUrl = new URL(value, window.location.origin);
			} catch (e) {
				return null;
			}

			if (targetUrl.origin !== window.location.origin) {
				return null;
			}

			targetUrl.searchParams.delete('dlck_rank_math_schema_preview');
			targetUrl.searchParams.delete('_dlck_preview_nonce');
			targetUrl.searchParams.delete('_dlck_cb');

			Object.keys(params).forEach(function (key) {
				if (typeof params[key] === 'undefined' || params[key] === null || params[key] === '') {
					return;
				}
				targetUrl.searchParams.set(key, String(params[key]));
			});

			return targetUrl.toString();
		}

		function fetchHtml(targetUrl) {
			return window.fetch(targetUrl, {
				method: 'GET',
				credentials: 'same-origin',
				cache: 'no-store'
			}).then(function (response) {
				if (!response.ok) {
					throw new Error('HTTP ' + response.status);
				}
				return response.text();
			});
		}

		function parseJsonLdPayloads(html) {
			var parser = new window.DOMParser();
			var doc = parser.parseFromString(String(html || ''), 'text/html');
			var rankMathScripts = Array.prototype.slice.call(
				doc.querySelectorAll('script[type="application/ld+json"].rank-math-schema, script[type="application/ld+json"].rank-math-schema-pro')
			);
			var scriptNodes = rankMathScripts.length ? rankMathScripts : Array.prototype.slice.call(
				doc.querySelectorAll('script[type="application/ld+json"]')
			);
			var payloads = [];
			var parseIssues = 0;
			var i;
			var text;

			if (!scriptNodes.length) {
				return {
					error: 'No JSON-LD scripts were found on that page.',
					payloads: [],
					parseIssues: 0,
					rankMathCount: 0
				};
			}

			for (i = 0; i < scriptNodes.length; i++) {
				text = $.trim(String(scriptNodes[i].textContent || ''));
				if (!text) {
					continue;
				}

				try {
					payloads.push(JSON.parse(text));
				} catch (error) {
					parseIssues++;
					payloads.push({
						_parseError: 'Could not parse this JSON-LD block.',
						_raw: text
					});
				}
			}

			if (!payloads.length) {
				return {
					error: 'JSON-LD script tags were found, but they were empty.',
					payloads: [],
					parseIssues: 0,
					rankMathCount: rankMathScripts.length
				};
			}

			return {
				payloads: payloads,
				parseIssues: parseIssues,
				rankMathCount: rankMathScripts.length,
				error: ''
			};
		}

		function flattenSchemaEntities(payloads) {
			var blocks = Array.isArray(payloads) ? payloads : [payloads];
			var entities = [];

			blocks.forEach(function (block, blockIndex) {
				if (!block || typeof block !== 'object') {
					return;
				}

				if (Array.isArray(block['@graph'])) {
					block['@graph'].forEach(function (entity, entityIndex) {
						if (!entity || typeof entity !== 'object') {
							return;
						}

						entities.push({
							entity: entity,
							key: String(entity['@id'] || '') || ('graph:' + blockIndex + ':' + entityIndex)
						});
					});
					return;
				}

				entities.push({
					entity: block,
					key: String(block['@id'] || '') || ('block:' + blockIndex)
				});
			});

			return entities;
		}

		function normalizeEntityType(entity) {
			var type = entity && entity['@type'];

			if (Array.isArray(type)) {
				return type.join(', ');
			}

			return String(type || 'Unknown');
		}

		function entityLabel(entityRecord) {
			var entity = entityRecord && entityRecord.entity ? entityRecord.entity : {};
			var id = String(entity['@id'] || '');
			var type = normalizeEntityType(entity);

			if (id) {
				return id + ' (' + type + ')';
			}

			return type;
		}

		function buildEntityMap(entities) {
			var map = {};
			(entities || []).forEach(function (record) {
				map[record.key] = record;
			});
			return map;
		}

		function summarizeSchemaDiff(basePayloads, finalPayloads, ignoreKeys) {
			var baseEntities = flattenSchemaEntities(basePayloads);
			var finalEntities = flattenSchemaEntities(finalPayloads);
			var baseMap = buildEntityMap(baseEntities);
			var lines = [];
			var ignored = Array.isArray(ignoreKeys) ? ignoreKeys : [];

			finalEntities.forEach(function (record) {
				var finalEntity = record.entity || {};
				var baseEntityRecord = baseMap[record.key];
				var baseEntity = baseEntityRecord ? (baseEntityRecord.entity || {}) : null;
				var addedKeys = [];
				var changedKeys = [];

				if (!baseEntity) {
					lines.push('- Added entity: ' + entityLabel(record));
					return;
				}

				Object.keys(finalEntity).forEach(function (key) {
					if (ignored.indexOf(String(key || '').toLowerCase()) !== -1) {
						return;
					}

					if (!Object.prototype.hasOwnProperty.call(baseEntity, key)) {
						addedKeys.push(key);
						return;
					}

					if (JSON.stringify(baseEntity[key]) !== JSON.stringify(finalEntity[key])) {
						changedKeys.push(key);
					}
				});

				if (!addedKeys.length && !changedKeys.length) {
					return;
				}

				lines.push(
					'- ' + entityLabel(record) + ': ' +
					[
						addedKeys.length ? ('added ' + addedKeys.join(', ')) : '',
						changedKeys.length ? ('changed ' + changedKeys.join(', ')) : ''
					].filter(function (value) {
						return value !== '';
					}).join('; ')
				);
			});

			if (!lines.length) {
				return ignored.length
					? 'No LC Tweaks schema differences were detected after applying the current diff-summary filter keys.'
					: 'No LC Tweaks schema differences were detected against the Rank Math base graph for this URL.';
			}

			return (
				'LC Tweaks additions / changes vs Rank Math base' +
				(ignored.length ? ' (ignoring: ' + ignored.join(', ') + ')' : '') +
				':\n' +
				lines.join('\n')
			);
		}

		function buildPreviewStatus(targetUrl, rankMathCount, diffAvailable, ignoreKeys, parseIssueCount) {
			var prefix = rankMathCount ? 'Showing Rank Math JSON-LD from ' : 'Showing all JSON-LD scripts from ';
			var suffix = '';
			var ignored = Array.isArray(ignoreKeys) ? ignoreKeys : [];

			if (!diffAvailable) {
				suffix = ' (diff summary unavailable because one of the preview payloads could not be parsed cleanly';
				if (parseIssueCount > 0) {
					suffix += ': ' + parseIssueCount + ' block' + (parseIssueCount === 1 ? '' : 's') + ' had parse issues';
				}
				suffix += ').';
				return prefix + targetUrl + suffix;
			}

			return prefix + targetUrl + ' with an admin-only diff against the Rank Math base graph.' + (ignored.length ? ' Summary filter active.' : '');
		}

		function refreshDiffFromCache() {
			var ignoreKeys;

			if (!lastPreviewDiffAvailable || !lastPreviewUrl || !lastBasePayloads || !lastFinalPayloads) {
				return;
			}

			ignoreKeys = parseIgnoreKeys(hasElement($ignoreKeys) ? $ignoreKeys.val() : '');
			setDiff(summarizeSchemaDiff(lastBasePayloads, lastFinalPayloads, ignoreKeys));
			setStatus(buildPreviewStatus(lastPreviewUrl, lastPreviewRankMathCount, true, ignoreKeys, 0), false);
		}

		renderIgnoreKeys(parseIgnoreKeys(String(window.localStorage.getItem(storageIgnoreKeysKey) || (hasElement($ignoreKeys) ? $ignoreKeys.val() : '') || '')));
		if (hasElement($ignoreKeys)) {
			$ignoreKeys.on('input change', function () {
				renderIgnoreKeys(parseIgnoreKeys($ignoreKeys.val()));
				refreshDiffFromCache();
			});
		}
		if (hasElement($chips)) {
			$chips.on('click', function (e) {
				var key = String($(this).data('key') || '').toLowerCase();
				var ignoreKeys = parseIgnoreKeys(hasElement($ignoreKeys) ? $ignoreKeys.val() : '');

				e.preventDefault();

				if (!key) {
					return;
				}

				if (ignoreKeys.indexOf(key) === -1) {
					ignoreKeys.push(key);
				} else {
					ignoreKeys = ignoreKeys.filter(function (item) {
						return item !== key;
					});
				}

				renderIgnoreKeys(dlckUniqueStrings(ignoreKeys));
				refreshDiffFromCache();
			});
		}

		function copyText(text, successMessage, errorMessage) {
			var value = String(text || '');

			if (!value) {
				return;
			}

			if (navigator.clipboard && navigator.clipboard.writeText) {
				navigator.clipboard.writeText(value).then(function () {
					setStatus(String(successMessage || 'Copied preview output to the clipboard.'), false);
				}).catch(function () {
					copyTextFallback(value, successMessage, errorMessage);
				});
				return;
			}

			copyTextFallback(value, successMessage, errorMessage);
		}

		function copyTextFallback(text, successMessage, errorMessage) {
			var value = String(text || '');
			var $temp;

			if (!value) {
				return;
			}

			$temp = $('<textarea>');
			$temp
				.css({
					position: 'absolute',
					left: '-9999px',
					top: (window.pageYOffset || document.documentElement.scrollTop) + 'px'
				})
				.attr('readonly', '')
				.val(value);
			$('body').append($temp);
			$temp[0].select();

			try {
				document.execCommand('copy');
				setStatus(String(successMessage || 'Copied preview output to the clipboard.'), false);
			} catch (e) {
				setStatus(String(errorMessage || 'Could not copy the preview output.'), true);
			}

			$temp.remove();
		}

		function buildPreviewReport() {
			var diffText = String($diff.text() || '');

			if (!lastPreviewUrl || !diffText) {
				return '';
			}

			return 'Schema preview URL: ' + lastPreviewUrl + '\n\n' + diffText;
		}

		$run.on('click', function (e) {
			var cacheBuster = String(Date.now());
			var ignoreKeys = parseIgnoreKeys(hasElement($ignoreKeys) ? $ignoreKeys.val() : '');
			var canonicalTargetUrl = buildTargetUrl($url.val());
			var diffAvailable = canPreviewDiff();
			var targetUrl = buildTargetUrl($url.val(), {
				_dlck_cb: cacheBuster
			});
			var baseUrl = diffAvailable ? buildTargetUrl($url.val(), {
				dlck_rank_math_schema_preview: 'base',
				_dlck_preview_nonce: previewNonce,
				_dlck_cb: cacheBuster + '-base'
			}) : null;

			e.preventDefault();

			if (!canonicalTargetUrl || !targetUrl) {
				lastPreviewUrl = '';
				lastPreviewRankMathCount = 0;
				lastPreviewDiffAvailable = false;
				lastBasePayloads = null;
				lastFinalPayloads = null;
				setDiff('');
				setOutput('');
				setStatus('Use a frontend URL on this site. Relative paths and same-origin absolute URLs are supported.', true);
				return;
			}

			if (diffAvailable && !baseUrl) {
				lastPreviewUrl = '';
				lastPreviewRankMathCount = 0;
				lastPreviewDiffAvailable = false;
				lastBasePayloads = null;
				lastFinalPayloads = null;
				setDiff('');
				setOutput('');
				setStatus('Could not build the Rank Math base preview URL.', true);
				return;
			}

			$run.prop('disabled', true).addClass('disabled');
			lastPreviewUrl = '';
			lastPreviewRankMathCount = 0;
			lastPreviewDiffAvailable = false;
			lastBasePayloads = null;
			lastFinalPayloads = null;
			setDiff('');
			setOutput('');
			setStatus(diffAvailable ? 'Fetching Rank Math base and final JSON-LD...' : 'Fetching final JSON-LD...', false);

			Promise.all(diffAvailable ? [
				fetchHtml(targetUrl),
				fetchHtml(baseUrl)
			] : [
				fetchHtml(targetUrl)
			]).then(function (responses) {
				var finalResult = parseJsonLdPayloads(responses[0]);
				var baseResult = diffAvailable ? parseJsonLdPayloads(responses[1]) : null;
				var finalPayload = finalResult.payloads.length === 1 ? finalResult.payloads[0] : finalResult.payloads;
				var finalStatus;

				if (finalResult.error) {
					setDiff('');
					setOutput('');
					setStatus(finalResult.error, true);
					return;
				}

				setOutput(JSON.stringify(finalPayload, null, 2));
				lastPreviewUrl = canonicalTargetUrl;
				lastPreviewRankMathCount = finalResult.rankMathCount;
				lastFinalPayloads = finalResult.payloads;

				if (!diffAvailable) {
					lastPreviewDiffAvailable = false;
					setDiff('');
					finalStatus =
						(finalResult.rankMathCount ? 'Showing Rank Math JSON-LD from ' : 'Showing all JSON-LD scripts from ') +
						canonicalTargetUrl +
						' (diff summary unavailable in this admin view).';
					setStatus(finalStatus, false);
					return;
				}

				if (baseResult.error || baseResult.parseIssues > 0 || finalResult.parseIssues > 0) {
					lastPreviewDiffAvailable = false;
					setDiff('');
					finalStatus = buildPreviewStatus(
						canonicalTargetUrl,
						finalResult.rankMathCount,
						false,
						ignoreKeys,
						baseResult.parseIssues + finalResult.parseIssues
					);
					setStatus(finalStatus, false);
					return;
				}

				lastBasePayloads = baseResult.payloads;
				lastPreviewDiffAvailable = true;
				setDiff(summarizeSchemaDiff(baseResult.payloads, finalResult.payloads, ignoreKeys));
				finalStatus = buildPreviewStatus(canonicalTargetUrl, finalResult.rankMathCount, true, ignoreKeys, 0);
				setStatus(finalStatus, false);
			}).catch(function () {
				lastPreviewUrl = '';
				lastPreviewRankMathCount = 0;
				lastPreviewDiffAvailable = false;
				lastBasePayloads = null;
				lastFinalPayloads = null;
				setDiff('');
				setOutput('');
				setStatus('Could not fetch schema preview for that URL. Use a same-origin frontend URL that is accessible from this admin session.', true);
			}).finally(function () {
				$run.prop('disabled', false).removeClass('disabled');
			});
		});

		if (hasElement($copy)) {
			$copy.on('click', function (e) {
				e.preventDefault();
				copyText($output.text(), 'Copied final JSON-LD output to the clipboard.', 'Could not copy the final JSON-LD output.');
			});
		}

		if (hasElement($copyDiff)) {
			$copyDiff.on('click', function (e) {
				e.preventDefault();
				copyText($diff.text(), 'Copied the diff summary to the clipboard.', 'Could not copy the diff summary.');
			});
		}

		if (hasElement($copyReport)) {
			$copyReport.on('click', function (e) {
				e.preventDefault();
				copyText(buildPreviewReport(), 'Copied the preview URL and diff summary to the clipboard.', 'Could not copy the preview report.');
			});
		}
	}

	function dlckInitOptionFinder() {
		var storageFavoritesKey = 'dlck_option_favorites_v1';
		var storageChangedKey = 'dlck_option_recent_changes_v1';
		var favorites = dlckStorageGetObject(storageFavoritesKey);
		var changed = dlckStorageGetObject(storageChangedKey);
		var baselineStateByKey = {};
		var $activeTool = $('#dlck .tool.tool-active');
		if (!$activeTool.length) {
			return;
		}
		var isSettingsTab = $activeTool.attr('id') === 'settings';

		var $cards = $activeTool.find('.lc-kit').filter(function () {
			return $(this).find('.box-title h3').length > 0;
		});
		if (!$cards.length) {
			return;
		}

		if (!$activeTool.find('#dlck-option-tools').length) {
			var finderHtml = '' +
				'<div id="dlck-option-tools" class="dlck-option-tools">' +
					'<input type="search" id="dlck-option-search" class="dlck-option-search" placeholder="Quick find options in this tab..." />';
			if (!isSettingsTab) {
				finderHtml += '<label class="dlck-option-filter"><input type="checkbox" id="dlck-filter-favorites" /> Favorites</label>';
			}
			finderHtml += '' +
					'<label class="dlck-option-filter"><input type="checkbox" id="dlck-filter-changed" /> Recently changed</label>' +
					'<button type="button" id="dlck-clear-recent" class="dlck-settings-button">Clear Recent</button>' +
					'<span id="dlck-option-count" class="dlck-option-count"></span>' +
				'</div>';
			$activeTool.prepend(finderHtml);
		}

		function getCardKey($card, index) {
			var $input = $card.find('input[name^="dlck_"], select[name^="dlck_"], textarea[name^="dlck_"]').first();
			if ($input.length && $input.attr('name')) {
				return $input.attr('name');
			}
			var heading = $.trim($card.find('.box-title h3').first().text());
			if (heading) {
				return 'card_' + dlckSlugifyText(heading);
			}
			return 'card_index_' + index;
		}

		function isTruthyStore(map, key) {
			return map && Object.prototype.hasOwnProperty.call(map, key);
		}

		function getCardStateSignature($card) {
			var state = $card.find('input[name],select[name],textarea[name]')
				.not('input[type="file"]')
				.serializeArray()
				.map(function (item) {
					return String(item.name || '') + '=' + String(item.value || '');
				});
			state.sort();
			return state.join('&');
		}

		function markCardState($card, key) {
			var favorite = !isSettingsTab && isTruthyStore(favorites, key);
			var recent = isTruthyStore(changed, key);
			$card.toggleClass('dlck-option-favorite', favorite);
			$card.toggleClass('dlck-option-changed', recent);

			var $star = $card.find('.dlck-favorite-toggle');
			if (isSettingsTab && $star.length) {
				$star.remove();
				$star = $();
			}
			if ($star.length) {
				$star.attr('aria-pressed', favorite ? 'true' : 'false');
				$star.toggleClass('is-active', favorite);
			}

			var $recentBadge = $card.find('.dlck-recent-badge');
			if (!$recentBadge.length) {
				$recentBadge = $('<span class="dlck-recent-badge">Recent change</span>');
				$card.find('.box-title').first().append($recentBadge);
			}
			$recentBadge.toggle(recent);
		}

		$cards.each(function (index) {
			var $card = $(this);
			var key = getCardKey($card, index);
			$card.addClass('dlck-option-card');
			$card.attr('data-dlck-option-key', key);

			if (!isSettingsTab && !$card.find('.dlck-favorite-toggle').length) {
				var $starButton = $('<button type="button" class="dlck-favorite-toggle" aria-label="Toggle favorite option" aria-pressed="false">★</button>');
				$card.find('.box-title').first().append($starButton);
			}

			if (!$card.attr('data-dlck-search-text')) {
				var searchText = $.trim(
					$card.find('.box-title h3').first().text() + ' ' +
					$card.find('.box-descr').first().text()
				).toLowerCase();
				$card.attr('data-dlck-search-text', searchText);
			}

			baselineStateByKey[key] = getCardStateSignature($card);
			markCardState($card, key);

			if (!isSettingsTab) {
				$card.on('click', '.dlck-favorite-toggle', function (e) {
					e.preventDefault();
					if (isTruthyStore(favorites, key)) {
						delete favorites[key];
					} else {
						favorites[key] = 1;
					}
					dlckStorageSetObject(storageFavoritesKey, favorites);
					markCardState($card, key);
					applyFilters();
				});
			}

			$card.on('change', 'input,select,textarea', function () {
				var currentState = getCardStateSignature($card);
				if (currentState !== baselineStateByKey[key]) {
					changed[key] = Date.now();
				} else {
					delete changed[key];
				}
				dlckStorageSetObject(storageChangedKey, changed);
				markCardState($card, key);
				applyFilters();
			});
		});

		function syncLinkedInfoPanel($card, shouldShow) {
			var $linkedPanel = $card.next('.dlck-hide');
			if (!$linkedPanel.length) {
				return;
			}

			if (!shouldShow) {
				$linkedPanel.attr('data-dlck-filter-hidden', '1').hide();
				return;
			}

			if ($linkedPanel.attr('data-dlck-filter-hidden') === '1') {
				$linkedPanel.removeAttr('data-dlck-filter-hidden');
				var isOn = $card.find('.hurkanSwitch-switch-item-status-on.active').length > 0;
				if (isOn) {
					$linkedPanel.show();
				} else {
					$linkedPanel.hide();
				}
			}
		}

		function applyFilters() {
			var query = $.trim($('#dlck-option-search').val() || '').toLowerCase();
			var favoritesOnly = !isSettingsTab && $('#dlck-filter-favorites').is(':checked');
			var changedOnly = $('#dlck-filter-changed').is(':checked');
			var total = 0;
			var shown = 0;

			$cards.each(function () {
				var $card = $(this);
				var key = String($card.attr('data-dlck-option-key') || '');
				var searchText = String($card.attr('data-dlck-search-text') || '');
				total++;

				var matchesSearch = !query || searchText.indexOf(query) !== -1;
				var matchesFavorite = !favoritesOnly || isTruthyStore(favorites, key);
				var matchesChanged = !changedOnly || isTruthyStore(changed, key);
				var visible = matchesSearch && matchesFavorite && matchesChanged;

				$card.toggle(visible);
				syncLinkedInfoPanel($card, visible);
				if (visible) {
					shown++;
				}
			});

			$('#dlck-option-count').text(shown + ' / ' + total + ' options');
		}

		$('#dlck-option-search').on('input', applyFilters);
		if (!isSettingsTab) {
			$('#dlck-filter-favorites, #dlck-filter-changed').on('change', applyFilters);
		} else {
			$('#dlck-filter-changed').on('change', applyFilters);
		}
		$('#dlck-clear-recent').on('click', function (e) {
			e.preventDefault();
			changed = {};
			dlckStorageSetObject(storageChangedKey, changed);
			$cards.each(function () {
				var $card = $(this);
				var key = String($card.attr('data-dlck-option-key') || '');
				markCardState($card, key);
			});
			applyFilters();
		});

		applyFilters();
	}

	function dlckInitScopeRulesVisibility() {
		var $enabled = $('input[name="dlck_scope_rules_enabled"]');
		var $optionsWrap = $('#dlck_scope_rules_options_wrap');
		if (!$enabled.length || !$optionsWrap.length) {
			return;
		}

		function syncVisibility(animate) {
			var showOptions = $enabled.is(':checked');
			if (!animate) {
				$optionsWrap.toggle(showOptions);
				return;
			}
			if (showOptions) {
				$optionsWrap.stop(true, true).slideDown(160);
			} else {
				$optionsWrap.stop(true, true).slideUp(160);
			}
		}

		$enabled.on('change', function () {
			syncVisibility(true);
		});
		syncVisibility(false);
	}

	function dlckInitPresetControls() {
		var $presetSelect = $('#dlck_preset_key');
		var $applyPreset = $('#dlck_apply_preset_submit');
		if (!$presetSelect.length || !$applyPreset.length) {
			return;
		}

		function syncApplyState() {
			var hasSelection = $.trim(String($presetSelect.val() || '')) !== '';
			$applyPreset.prop('disabled', !hasSelection).toggleClass('disabled', !hasSelection);
		}

		$presetSelect.on('change input', syncApplyState);
		syncApplyState();
	}

	function dlckInitScopeRulesLiveValidation() {
		var $wrapper = $('#dlck_scope_rules_live_validation');
		var $enabled = $('input[name="dlck_scope_rules_enabled"]');
		var $options = $('#dlck_scope_rules_options');
		var $loggedState = $('#dlck_scope_rules_logged_state');
		var $roles = $('#dlck_scope_rules_roles');
		var $include = $('#dlck_scope_rules_include_paths');
		var $exclude = $('#dlck_scope_rules_exclude_paths');
		var $knownList = $('#dlck_scope_known_options_list');

		if (
			!$wrapper.length ||
			!$enabled.length ||
			!$options.length ||
			!$loggedState.length ||
			!$roles.length ||
			!$include.length ||
			!$exclude.length
		) {
			return;
		}

		var knownOptions = {};
		if ($knownList.length) {
			$knownList.find('option').each(function () {
				var value = dlckSanitizeKey($(this).attr('value') || $(this).val() || '');
				if (/^dlck_[a-z0-9_]+$/.test(value)) {
					knownOptions[value] = true;
				}
			});
		}

		function toPreviewList(values) {
			var list = dlckUniqueStrings(values).slice(0, 6);
			return list.join(', ');
		}

		function render(messages, stateClass) {
			var html = '<strong>Live Validation</strong>';
			if (!messages.length) {
				html += '<div class="dlck-scope-live-row">No issues detected.</div>';
			} else {
				html += '<ul>';
				messages.forEach(function (message) {
					html += '<li>' + String(message || '') + '</li>';
				});
				html += '</ul>';
			}

			$wrapper
				.removeClass('is-ok is-warning is-info')
				.addClass(stateClass || 'is-ok')
				.html(html);
		}

		function runValidation() {
			var isEnabled = $enabled.is(':checked');
			var warnings = [];
			var info = [];

			if (!isEnabled) {
				render(['Scope Rules is currently disabled.'], 'is-info');
				return;
			}

			var rawOptionLines = dlckParseLineList($options.val());
			var validOptionKeys = [];
			var invalidOptionKeys = [];
			rawOptionLines.forEach(function (line) {
				var key = dlckSanitizeKey(line);
				if (!/^dlck_[a-z0-9_]+$/.test(key)) {
					invalidOptionKeys.push(line);
					return;
				}
				validOptionKeys.push(key);
			});
			validOptionKeys = dlckUniqueStrings(validOptionKeys);
			invalidOptionKeys = dlckUniqueStrings(invalidOptionKeys);

			if (!validOptionKeys.length) {
				warnings.push('Scope Rules is enabled but no valid target option keys are listed.');
			}

			if (invalidOptionKeys.length) {
				warnings.push('Some target keys are not valid dlck_* keys: ' + toPreviewList(invalidOptionKeys) + '.');
			}

			if (Object.keys(knownOptions).length) {
				var unknownKeys = validOptionKeys.filter(function (key) {
					return !knownOptions[key];
				});
				if (unknownKeys.length) {
					warnings.push('Unknown target option keys: ' + toPreviewList(unknownKeys) + '.');
				}
			}

			var loggedState = String($loggedState.val() || 'all');
			var roleRules = dlckUniqueStrings(dlckParseLineList($roles.val()).map(dlckSanitizeKey));
			if (loggedState === 'logged_out' && roleRules.length) {
				warnings.push('Roles are set while "Logged-out users only" is selected. Roles only apply to logged-in users.');
			}

			var includeRules = dlckUniqueStrings(dlckParseLineList($include.val()).map(dlckNormalizePath));
			var excludeRules = dlckUniqueStrings(dlckParseLineList($exclude.val()).map(dlckNormalizePath));
			var overlapRules = includeRules.filter(function (rule) {
				return excludeRules.indexOf(rule) !== -1;
			});
			if (overlapRules.length) {
				warnings.push('Matching include/exclude paths detected: ' + toPreviewList(overlapRules) + '. Exclude paths take priority.');
			}

			if (loggedState === 'all' && !roleRules.length && !includeRules.length && !excludeRules.length) {
				info.push('No path, role, or login-state restrictions set. Targeted options will run globally.');
			}

			if (warnings.length) {
				render(warnings.concat(info), 'is-warning');
			} else if (info.length) {
				render(info, 'is-info');
			} else {
				render([], 'is-ok');
			}
		}

		var $watchFields = $enabled
			.add($options)
			.add($loggedState)
			.add($roles)
			.add($include)
			.add($exclude);

		$watchFields.on('input change', runValidation);
		runValidation();
	}

	function dlckInitScopeRulesTester() {
		var $option = $('#dlck_scope_test_option');
		var $path = $('#dlck_scope_test_path');
		var $useCurrentPath = $('#dlck_scope_test_use_current_path');
		var $state = $('#dlck_scope_test_user_state');
		var $roles = $('#dlck_scope_test_roles');
		var $run = $('#dlck_scope_test_run');
		var $result = $('#dlck_scope_test_result');

		if (!$option.length || !$path.length || !$state.length || !$roles.length || !$run.length || !$result.length) {
			return;
		}

		function setResult(text, cls) {
			$result.removeClass('is-allowed is-blocked is-error');
			if (cls) {
				$result.addClass(cls);
			}
			$result.text(text);
		}

		if ($useCurrentPath.length) {
			$useCurrentPath.on('click', function (e) {
				e.preventDefault();
				var currentPath = dlckNormalizePath(
					(window.location && window.location.pathname) ? window.location.pathname : '/'
				);
				$path.val(currentPath);
				if (currentPath.indexOf('/wp-admin') === 0) {
					setResult(
						'Request path set to current browser path: ' + currentPath + '\nTip: for frontend testing, enter a frontend path like /shop/.',
						''
					);
					return;
				}
				setResult('Request path set to current browser path: ' + currentPath, '');
			});
		}

		$run.on('click', function (e) {
			e.preventDefault();
			var optionName = $.trim($option.val() || '');
			var requestPath = dlckNormalizePath($.trim($path.val() || '/'));
			$path.val(requestPath);
			if (!optionName) {
				setResult('Enter an option key to run a scope test.', 'is-error');
				return;
			}

			$run.prop('disabled', true).addClass('disabled');
			setResult('Running scope test...', '');

			$.post(
				ajaxurl,
				{
					action: 'dlck_scope_rules_test',
					nonce: (window.dlck_admin && window.dlck_admin.scope_test_nonce) ? window.dlck_admin.scope_test_nonce : '',
					option_name: optionName,
					request_path: requestPath,
					user_state: String($state.val() || 'logged_out'),
					user_roles: String($roles.val() || '')
				}
			).done(function (response) {
				if (response && response.success && response.data) {
					var cls = response.data.allowed ? 'is-allowed' : 'is-blocked';
					var text = String(response.data.summary || '');
					if (response.data.reason) {
						text += (text ? '\n' : '') + String(response.data.reason);
					}
					setResult(text || 'Scope test completed.', cls);
					return;
				}
				setResult((response && response.data) ? String(response.data) : 'Could not run scope test.', 'is-error');
			}).fail(function () {
				setResult('Could not run scope test.', 'is-error');
			}).always(function () {
				$run.prop('disabled', false).removeClass('disabled');
			});
		});
	}
	dlckInitOptionFinder();
	dlckInitRankMathSchemaPreviewTool();
	dlckInitScopeRulesVisibility();
	dlckInitPresetControls();
	dlckInitScopeRulesLiveValidation();
	dlckInitScopeRulesTester();

	$('#dlck-clear-cache').off('click').on('click', function (e) {
		e.preventDefault();
		var $btn = $(this);
		var $result = $('#dlck-clear-cache-result');

		dlck_ajax_saving.css('display','block');
		dlck_ajax_loader.css('display','block');
		$btn.prop('disabled', true).addClass('disabled');
		$result.hide().removeClass('success error');

		$.post(
			ajaxurl,
			{
				action: 'dlck_clear_cache_files',
				_wpnonce: dlck_admin.nonce || ''
			}
		).done(function (response) {
			dlck_ajax_loader.css('display','none');
			dlck_ajax_saving.addClass('success-animation');
			setTimeout(function(){
				dlck_ajax_saving.removeClass('success-animation').css('display','none');
			}, 1200);

			if (response && response.success) {
				$result.text(response.data || 'Cache cleared.').addClass('success').show();
			} else {
				$result.text((response && response.data) ? response.data : 'Could not clear cache.').addClass('error').show();
			}
		}).fail(function () {
			dlck_ajax_loader.css('display','none');
			dlck_ajax_saving.removeClass('success-animation').css('display','none');
			$result.text('Could not clear cache.').addClass('error').show();
		}).always(function () {
			$btn.prop('disabled', false).removeClass('disabled');
		});
	});

	$('#dlck-clear-lazy-cache').off('click').on('click', function (e) {
		e.preventDefault();
		var $btn = $(this);
		var $result = $('#dlck-clear-lazy-cache-result');

		dlck_ajax_saving.css('display','block');
		dlck_ajax_loader.css('display','block');
		$btn.prop('disabled', true).addClass('disabled');
		$result.hide().removeClass('success error');

		$.post(
			ajaxurl,
			{
				action: 'dlck_clear_lazy_cache',
				_wpnonce: (dlck_admin && dlck_admin.lazy_nonce) ? dlck_admin.lazy_nonce : ''
			}
		).done(function (response) {
			dlck_ajax_loader.css('display','none');
			dlck_ajax_saving.addClass('success-animation');
			setTimeout(function(){
				dlck_ajax_saving.removeClass('success-animation').css('display','none');
			}, 1200);

			if (response && response.success) {
				$result.text(response.data || 'Lazy cache cleared.').addClass('success').show();
			} else {
				$result.text((response && response.data) ? response.data : 'Could not clear lazy cache.').addClass('error').show();
			}
		}).fail(function () {
			dlck_ajax_loader.css('display','none');
			dlck_ajax_saving.removeClass('success-animation').css('display','none');
			$result.text('Could not clear lazy cache.').addClass('error').show();
		}).always(function () {
			$btn.prop('disabled', false).removeClass('disabled');
		});
	});
	$('.dlck-color-field').wpColorPicker();
	
	$('#dlck_export_box > input[type="checkbox"]').on('change',function(){
		if(!$('#dlck_export_box > input[type="checkbox"]')[0].checked && !$('#dlck_export_box > input[type="checkbox"]')[1].checked){
			$('#dlck_export_submit').attr('disabled','disabled');;
		}
		else{
			$('#dlck_export_submit').removeAttr('disabled');
		}
	});

	var $form  = $('.et-divi-lc-kit-form');
	function dlckNormalizeCheckboxes() {
		if (!$form.length) {
			return;
		}
		$form.find('input[type="hidden"][data-dlck-checkbox]').remove();
		$form.find('input[type="checkbox"]').each(function () {
			var $checkbox = $(this);
			var name = $checkbox.attr('name');
			if (!name) {
				return;
			}
			if (!$checkbox.prop('checked')) {
				$('<input>', {
					type: 'hidden',
					name: name,
					value: '0',
					'data-dlck-checkbox': name
				}).appendTo($form);
			}
		});
	}

	var dlckCustomMedia = true;
	var dlckOrigSendAttachment = wp.media && wp.media.editor ? wp.media.editor.send.attachment : null;
	$(document).on('click', '.upload_image_button', function (e) {
		e.preventDefault();
		if (!wp.media || !wp.media.editor) {
			return;
		}
		var $button = $(this);
		var $imgInput = $button.prev('.background_image');
		dlckCustomMedia = true;

		wp.media.editor.send.attachment = function (props, attachment) {
			if (dlckCustomMedia && $imgInput.length) {
				var size = props && props.size ? props.size : 'full';
				var url = attachment && attachment.url ? attachment.url : '';
				if (attachment && attachment.sizes && attachment.sizes[size]) {
					url = attachment.sizes[size].url;
				}
				if (url) {
					$imgInput.val(url);
				}
			} else if (dlckOrigSendAttachment) {
				dlckOrigSendAttachment.apply(this, [props, attachment]);
			}
		};

		wp.media.editor.open($button);
	});

	$(document).on('click', '.add_media', function () {
		dlckCustomMedia = false;
	});

	var $customIconsList = $('.dlck-custom-icons-list');
	if ($customIconsList.length) {
		var inputName = $customIconsList.data('input-name') || 'dlck_divi_custom_icon_urls';
		var placeholderText = $customIconsList.data('placeholder') || 'Image URL';
		var chooseLabel = $customIconsList.data('choose-label') || 'Choose Image';
		var removeLabel = $customIconsList.data('remove-label') || 'Remove';
		var nextIndex = parseInt($customIconsList.data('next-index'), 10);
		if (isNaN(nextIndex)) {
			nextIndex = 0;
		}
		function buildCustomIconRow(index) {
			return '<p class="dlck-custom-icon-row">' +
				'<input type="url" class="background_image" size="36" maxlength="1024" placeholder="' + placeholderText + '" name="' + inputName + '[' + index + ']" value="" />' +
				'<button type="button" class="button upload_image_button">' + chooseLabel + '</button>' +
				'<button type="button" class="button dlck-remove-custom-icon">' + removeLabel + '</button>' +
				'</p>';
		}
		$('.dlck-add-custom-icon').on('click', function () {
			$customIconsList.append(buildCustomIconRow(nextIndex));
			nextIndex += 1;
			$customIconsList.data('next-index', nextIndex);
		});
		$customIconsList.on('click', '.dlck-remove-custom-icon', function (e) {
			e.preventDefault();
			var $row = $(this).closest('.dlck-custom-icon-row');
			$row.find('.background_image').val('');
			$row.hide();
		});
	}

	// Make related YouTube toggles mutually exclusive in the UI.
	var $ytChannelRestrict = $('input[name="dlck_divi_hide_related_video_suggestions"]');
	var $ytOverlayHide     = $('input[name="dlck_divi_disable_related_video_suggestions"]');
	function dlckSyncYtToggles(changed, other) {
		if (changed.prop('checked')) {
			other.prop('checked', false);
			other.prop('disabled', true);
			changed.prop('disabled', false);
		} else {
			other.prop('disabled', false);
		}
	}
	// Normalize initial state in case both were previously on.
	if ($ytChannelRestrict.prop('checked') && $ytOverlayHide.prop('checked')) {
		$ytChannelRestrict.prop('checked', false);
	}
	// Apply initial disabling if one is on.
	dlckSyncYtToggles($ytChannelRestrict, $ytOverlayHide);
	dlckSyncYtToggles($ytOverlayHide, $ytChannelRestrict);

	$ytChannelRestrict.on('change', function () { dlckSyncYtToggles($ytChannelRestrict, $ytOverlayHide); });
	$ytOverlayHide.on('change', function () { dlckSyncYtToggles($ytOverlayHide, $ytChannelRestrict); });

	// Make lazy load "load all" toggles mutually exclusive.
	var $lazyLoadInteraction = $('input[name="dlck_divi_lazy_load_all_on_interaction"]');
	var $lazyLoadIdle        = $('input[name="dlck_divi_lazy_load_all_on_idle"]');
	function dlckSyncLazyLoadAll(changed, other) {
		if (changed.prop('checked')) {
			other.prop('checked', false);
			other.prop('disabled', true);
			changed.prop('disabled', false);
		} else {
			other.prop('disabled', false);
		}
	}
	if ($lazyLoadInteraction.prop('checked') && $lazyLoadIdle.prop('checked')) {
		$lazyLoadIdle.prop('checked', false);
	}
	dlckSyncLazyLoadAll($lazyLoadInteraction, $lazyLoadIdle);
	dlckSyncLazyLoadAll($lazyLoadIdle, $lazyLoadInteraction);
	$lazyLoadInteraction.on('change', function () { dlckSyncLazyLoadAll($lazyLoadInteraction, $lazyLoadIdle); });
	$lazyLoadIdle.on('change', function () { dlckSyncLazyLoadAll($lazyLoadIdle, $lazyLoadInteraction); });

	// Make Woo remove-files toggles mutually exclusive (prefer "all files").
	var $wooSafe = $('input[name="dlck_remove_woo_files"]');
	var $wooAll  = $('input[name="dlck_remove_woo_all_files"]');
	function dlckSyncWooToggles(changed, other) {
		if (changed.prop('checked')) {
			other.prop('checked', false);
			other.prop('disabled', true);
			changed.prop('disabled', false);
		} else {
			other.prop('disabled', false);
		}
	}
	if ($wooSafe.prop('checked') && $wooAll.prop('checked')) {
		$wooSafe.prop('checked', false);
	}
	dlckSyncWooToggles($wooSafe, $wooAll);
	dlckSyncWooToggles($wooAll, $wooSafe);
	$wooSafe.on('change', function(){ dlckSyncWooToggles($wooSafe, $wooAll); });
	$wooAll.on('change', function(){ dlckSyncWooToggles($wooAll, $wooSafe); });

	// Disable YITH exclusion toggles unless the activator is on.
	var $yithActivator = $('input[name="dlck_yith_activator"]');
	var $yithExcludes = $(
		'input[name="dlck_yith_activator_exclude_membership"],' +
		'input[name="dlck_yith_activator_exclude_compare"],' +
		'input[name="dlck_yith_activator_exclude_wishlist"]'
	);
	function dlckSyncYithExcludes() {
		if (!$yithActivator.length || !$yithExcludes.length) {
			return;
		}
		if ($yithActivator.is(':checked')) {
			$yithExcludes.prop('disabled', false);
		} else {
			$yithExcludes.prop('checked', false).prop('disabled', true);
		}
	}
	dlckSyncYithExcludes();
	$yithActivator.on('change', dlckSyncYithExcludes);

	var $unfilteredUploads = $('input[name="dlck_allow_unfiltered_uploads"]');
	var $svgUploads = $('input[name="dlck_svg_uploads"]');
	var $jsonUploads = $('input[name="dlck_json_uploads"]');
	var $fontUploads = $('input[name="dlck_ttf_uploads"]');
	function dlckSyncUploadToggles() {
		if (
			!$unfilteredUploads.length ||
			(!$svgUploads.length && !$jsonUploads.length && !$fontUploads.length)
		) {
			return;
		}
		if ($unfilteredUploads.is(':checked')) {
			$svgUploads.prop('checked', false).prop('disabled', true);
			$jsonUploads.prop('checked', false).prop('disabled', true);
			$fontUploads.prop('checked', false).prop('disabled', true);
		} else {
			$svgUploads.prop('disabled', false);
			$jsonUploads.prop('disabled', false);
			$fontUploads.prop('disabled', false);
		}
		$svgUploads.trigger('change');
		$jsonUploads.trigger('change');
		$fontUploads.trigger('change');
	}
	dlckSyncUploadToggles();
	$unfilteredUploads.on('change', dlckSyncUploadToggles);

	
	$(".post-meta, .archive #left-area .et_pb_post, .et_pb_title_meta_container").html(function () {
		return $(this).html().replace(/\|/g, '').replace('by', '').replace('...', '').replace(/,/g, '');
	});
	
	 if ($.fn.hurkanSwitch) {
	 
	 $('.checkbox').hurkanSwitch({
		'onTitle':'Enabled',
		'offTitle':'Disabled',
		'responsive':true

	});
	 
	 $('.on-off').hurkanSwitch({
		'onTitle':'ON',
		'offTitle':'OFF',
		'responsive':true

	});
	
	}
	
	// Show & Hide LC Kit Options
	$('.dlck-hide, .dlck-cust-link').not('.dlck-visible').hide();
	$('.trigger .hurkanSwitch-switch-item-status-on.active').closest('.trigger').next('.dlck-hide').show();
	$('.hurkanSwitch-switch-item-status-on.active').closest('.box-content').next('.dlck-cust-link').show();
	
	$('.trigger .minibox, .ico-trigger .minibox').click(function(){
	  if($('.hurkanSwitch-switch-item-status-on', this).hasClass('active')) {
		  $(this).parent('.trigger').next('.dlck-hide').show();
		  $(this).next('.dlck-cust-link').show();
		}
	  else{
	    $(this).parent('.trigger').next('.dlck-hide').hide();
		  $(this).next('.dlck-cust-link').hide();
	  }
	 });
	 
	$('.tool-section').next('.lc-kit').addClass('first');
	$('.tool-section:not(.first)').prev().addClass('last');
	
	// Mobile menu show/hide
	
	// show hide "hide top bar"
	$('.hidetopbar .hurkanSwitch-switch-item-status-on.active').parents('.hidetopbar').next().next('.dlck-hide').addClass('hidden');
	$('.hidetopbar .minibox').click(function(){
	  if($('.hurkanSwitch-switch-item-status-on', this).hasClass('active')) {
		  $(this).parent('.hidetopbar').next().next('.dlck-hide').addClass('hidden');
		}
	  else{
	    $(this).parent('.hidetopbar').next().next('.dlck-hide').removeClass('hidden');
	  }
	 });
	function dlckClearSubformTypeInputs() {
		$('#dlck_export_submit_hidden_input').remove();
		$('#dlck_import_submit_hidden_input').remove();
		$('#dlck_diagnostics_submit_hidden_input').remove();
		$('#dlck_restore_snapshot_submit_hidden_input').remove();
		$('#dlck_network_policy_submit_hidden_input').remove();
		$('#dlck_restore_preset_submit_hidden_input').remove();
		$('#dlck_apply_preset_submit_hidden_input').remove();
	}

	$('#dlck_export_submit').click(function(){
		dlckClearSubformTypeInputs();
		$('<input>').attr({
			type: 'hidden',
			name: 'dlck_subform_type',
			value: 'settings_export',
			id: 'dlck_export_submit_hidden_input'
		}).appendTo('#dlck_settings_form');
	});
	$('#dlck_import_submit').click(function(){
		dlckClearSubformTypeInputs();
		$('<input>').attr({
			type: 'hidden',
			name: 'dlck_subform_type',
			value: 'settings_import',
			id: 'dlck_import_submit_hidden_input'
		}).appendTo('#dlck_settings_form');
	});
	$('#dlck_diagnostics_submit').click(function(){
		dlckClearSubformTypeInputs();
		$('<input>').attr({
			type: 'hidden',
			name: 'dlck_subform_type',
			value: 'settings_diagnostics',
			id: 'dlck_diagnostics_submit_hidden_input'
		}).appendTo('#dlck_settings_form');
	});
	$('#dlck_restore_snapshot_submit').click(function(){
		dlckClearSubformTypeInputs();
		$('<input>').attr({
			type: 'hidden',
			name: 'dlck_subform_type',
			value: 'settings_restore_snapshot',
			id: 'dlck_restore_snapshot_submit_hidden_input'
		}).appendTo('#dlck_settings_form');
	});
	$('#dlck_network_policy_submit').click(function(){
		dlckClearSubformTypeInputs();
		$('<input>').attr({
			type: 'hidden',
			name: 'dlck_subform_type',
			value: 'settings_network_policy',
			id: 'dlck_network_policy_submit_hidden_input'
		}).appendTo('#dlck_settings_form');
	});
	$('#dlck_restore_preset_submit').click(function(){
		dlckClearSubformTypeInputs();
		$('<input>').attr({
			type: 'hidden',
			name: 'dlck_subform_type',
			value: 'settings_restore_preset',
			id: 'dlck_restore_preset_submit_hidden_input'
		}).appendTo('#dlck_settings_form');
	});
	$('#dlck_apply_preset_submit').click(function(){
		dlckClearSubformTypeInputs();
		$('<input>').attr({
			type: 'hidden',
			name: 'dlck_subform_type',
			value: 'settings_apply_preset',
			id: 'dlck_apply_preset_submit_hidden_input'
		}).appendTo('#dlck_settings_form');
	});
	$('#dlck-epanel-save').click(function(){
		dlckClearSubformTypeInputs();
	});
	$form.on('submit', function () {
		dlckNormalizeCheckboxes();
	});
	$('#dlck_settings_form input').on('keypress', function(event) {
		if(event.keyCode === 13){
			event.preventDefault()
		}
   });
   var dlck_custom_preloader_image = $('#dlck_custom_preloader_image');
   var dlck_custom_preloader_image_val = $('#dlck_custom_preloader_image').prop('checked');
   var dlck_preloaders_list = $('#dlck_preloaders_list');
   if(dlck_custom_preloader_image_val){
		dlck_preloaders_list.hide();
   }
   dlck_custom_preloader_image.on('change', function(){
		if($(this).prop('checked')){
			dlck_preloaders_list.hide();
		}
		else{
			dlck_preloaders_list.show();
		}
   });

});

jQuery(document).ready(function(){
	jQuery('.dlck-loader .status').delay(300).fadeOut('slow');
	jQuery('.dlck-loader').delay(300).fadeOut('slow');
	jQuery('#dlck .page-container').css('min-height','0');
});

// Click to copy CSS classes (enhanced: tooltip + aria-live, CSS kept in admin stylesheet)
jQuery(document).ready(function($) {
	// ensure an aria-live region exists for screen readers
	if ($('#dlck-copy-status-region').length === 0) {
		$('#dlck').append('<div id="dlck-copy-status-region" class="dlck-copy-status" aria-live="polite" aria-atomic="true"></div>');
	}

	// Delegate interactions so dynamically added content is covered too
	var selector = '#dlck div.info strong, #dlck .lc-kit.css .box-title h3';
	function isExcludedCopyTarget($el) {
		return $el.closest('.dlck-rank-math-schema-panel').length > 0;
	}

	// show a 'Click to copy' tooltip on hover
	$(document).on('mouseenter', selector, function(e){
		var $el = $(this);
		if (isExcludedCopyTarget($el)) return;
		// don't show tooltip when hovering links
		if ($el.find('a').length) return;
		showTooltip($el, 'Click to copy');
	});
	$(document).on('mouseleave', selector, function(e){
		hideTooltip();
	});

	// allow keyboard activation (Enter / Space)
	$(document).on('keydown', selector, function(e){
		if (e.key === 'Enter' || e.key === ' ' || e.key === 'Spacebar') {
			e.preventDefault();
			var $el = $(this);
			if (isExcludedCopyTarget($el)) return;
			copyToClipboard($el.text().trim(), $el);
		}
	});

	// click to copy
	$(document).on('click', selector, function(e) {
		var $el = $(this);
		if (isExcludedCopyTarget($el)) return;
		if ($(e.target).is('a') || $(e.target).closest('a').length) return;
		var text = $el.text().trim();
		if (!text) return;
		copyToClipboard(text, $el);
	});

	function copyToClipboard(text, $element) {
		if (navigator.clipboard && navigator.clipboard.writeText) {
			navigator.clipboard.writeText(text).then(function() {
				showCopiedFeedback($element, text);
			}).catch(function() {
				fallbackCopy(text, $element);
			});
		} else {
			fallbackCopy(text, $element);
		}
	}

	function fallbackCopy(text, $element) {
		var $ta = $('<textarea>');
		$ta.css({position: 'absolute', left: '-9999px', top: (window.pageYOffset || document.documentElement.scrollTop) + 'px'}).attr('readonly', '').val(text);
		$('body').append($ta);
		$ta[0].select();
		try {
			document.execCommand('copy');
			showCopiedFeedback($element, text);
		} catch (err) {
			$('#dlck-copy-status-region').text('Could not copy to clipboard');
		}
		$ta.remove();
	}

	function showCopiedFeedback($element, text) {
		// visual focus style
		$element.addClass('dlck-copied');
		setTimeout(function() { $element.removeClass('dlck-copied'); }, 1500);

		// show temporary tooltip with success message
		showTooltip($element, 'Copied to clipboard');
		setTimeout(hideTooltip, 1100);

		// aria-live feedback (English)
		$('#dlck-copy-status-region').text('Copied to clipboard: ' + text);
	}

	// Tooltip helpers
	var $currentTip = null;
	function showTooltip($element, text) {
		hideTooltip();
		$currentTip = $('<span class="dlck-copy-tooltip dlck-visible">' + text + '</span>');
		$('body').append($currentTip);
		var off = $element.offset();
		var tipW = $currentTip.outerWidth();
		var tipH = $currentTip.outerHeight();
		var left = off.left + ($element.outerWidth() - tipW) / 2;
		var top = off.top - tipH - 8;
		left = Math.max(8, left);
		$currentTip.css({left: left + 'px', top: top + 'px'});
	}
	function hideTooltip(){
		if ($currentTip) {
			$currentTip.remove();
			$currentTip = null;
		}
	}

});
