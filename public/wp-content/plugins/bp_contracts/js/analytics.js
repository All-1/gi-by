"use strict";
let analytics = document.getElementById('analytics');
const where = 'analytics';
let analyticsCharts = [];
let analyticsChartStates = [];
let analyticsHiddenEntityKeys = new Set();
let analyticsContractorMode = 'all';
let analyticsSelectedContractorKeys = new Set();
let analyticsUsersCatalog = {};
let analyticsWhoseMode = 'all';
let analyticsTimeMode = 'work_hours';
let analyticsSlaFirstMinutes = 180;
let analyticsSlaSubsequentMinutes = 120;
let analyticsSlaDebounceTimer = null;
let analyticsDealerDrilldownContext = null;
let analyticsDealerDrilldownParentSnapshot = null;
let analyticsWhoseModeBeforeDrilldown = null;

const ANALYTICS_SLA_MAX_MINUTES = 10080;

function splitMinutesToDuration(totalMinutes) {
  let total = Math.max(0, Math.round(Number(totalMinutes) || 0));
  return {
    days: Math.floor(total / 1440),
    hours: Math.floor((total % 1440) / 60),
    minutes: total % 60
  };
}

function readSlaDurationMinutes(target) {
  let daysInput = document.getElementById('sla_' + target + '_days');
  let hoursInput = document.getElementById('sla_' + target + '_hours');
  let minutesInput = document.getElementById('sla_' + target + '_minutes');
  if (!daysInput || !hoursInput || !minutesInput) {
    return 0;
  }
  let days = Math.max(0, Math.floor(Number(daysInput.value) || 0));
  let hours = Math.max(0, Math.floor(Number(hoursInput.value) || 0));
  let minutes = Math.max(0, Math.floor(Number(minutesInput.value) || 0));
  return Math.min(ANALYTICS_SLA_MAX_MINUTES, days * 1440 + hours * 60 + minutes);
}

function setSlaDurationInputs(target, totalMinutes) {
  let parts = splitMinutesToDuration(totalMinutes);
  let daysInput = document.getElementById('sla_' + target + '_days');
  let hoursInput = document.getElementById('sla_' + target + '_hours');
  let minutesInput = document.getElementById('sla_' + target + '_minutes');
  if (!daysInput || !hoursInput || !minutesInput) {
    return;
  }
  daysInput.value = parts.days;
  hoursInput.value = parts.hours;
  minutesInput.value = parts.minutes;
}

function normalizeSlaMinutes(totalMinutes, fallbackMinutes) {
  let minutes = Math.round(Number(totalMinutes) || 0);
  if (minutes <= 0) {
    minutes = fallbackMinutes;
  }
  return Math.max(1, Math.min(ANALYTICS_SLA_MAX_MINUTES, minutes));
}

function isDurationUnit(unit) {
  return unit === 'hours';
}

function formatDurationSeconds(seconds) {
  let total = Math.round(Number(seconds));
  if (!Number.isFinite(total) || total < 0) {
    return '-';
  }

  if (analyticsTimeMode === 'standard') {
    let days = Math.floor(total / 86400);
    let hours = Math.floor((total % 86400) / 3600);
    let parts = [];
    if (days > 0) {
      parts.push(days + ' д.');
    }
    if (hours > 0 || parts.length === 0) {
      parts.push(hours + ' ч.');
    }
    return parts.join(' ');
  }

  let hours = Math.floor(total / 3600);
  let minutes = Math.floor((total % 3600) / 60);
  let parts = [];
  if (hours > 0) {
    parts.push(hours + ' ч.');
  }
  if (minutes > 0 || parts.length === 0) {
    parts.push(minutes + ' мин.');
  }
  return parts.join(' ');
}

function getChartDurationValue(seconds) {
  let numberValue = Number(seconds);
  if (!Number.isFinite(numberValue)) {
    return null;
  }
  if (analyticsTimeMode === 'standard') {
    return Math.round((numberValue / 86400) * 100) / 100;
  }
  return Math.round((numberValue / 3600) * 100) / 100;
}

function parseDurationSeconds(value) {
  if (value === undefined || value === null || value === '-') {
    return null;
  }
  if (typeof value === 'number' && Number.isFinite(value)) {
    return value;
  }

  let text = String(value).trim();
  if (!text) {
    return null;
  }

  let dayMatch = text.match(/(\d+)\s*д\.?/i);
  let hourMatch = text.match(/(\d+)\s*ч\.?/i);
  let minMatch = text.match(/(\d+)\s*мин\.?/i);
  if (dayMatch || hourMatch || minMatch) {
    let seconds = 0;
    if (dayMatch) {
      seconds += Number(dayMatch[1]) * 86400;
    }
    if (hourMatch) {
      seconds += Number(hourMatch[1]) * 3600;
    }
    if (minMatch) {
      seconds += Number(minMatch[1]) * 60;
    }
    return seconds;
  }

  let numberValue = Number(text.replace(',', '.'));
  return Number.isFinite(numberValue) ? numberValue : null;
}

function formatDurationFromChartValue(chartValue) {
  let numberValue = Number(chartValue);
  if (!Number.isFinite(numberValue)) {
    return '-';
  }
  let seconds = analyticsTimeMode === 'standard'
    ? Math.round(numberValue * 86400)
    : Math.round(numberValue * 3600);
  return formatDurationSeconds(seconds);
}

function formatMetricCellValue(value, unit) {
  if (unit === 'count') {
    let numberValue = Number(value);
    return Number.isFinite(numberValue) ? numberValue : '-';
  }
  if (unit === 'ratio') {
    let numberValue = Number(value);
    return Number.isFinite(numberValue) ? Math.round(numberValue * 1000) / 1000 : '-';
  }
  if (unit === 'percent') {
    let numberValue = Number(value);
    return Number.isFinite(numberValue) ? formatAnalyticsValue(numberValue, 'percent') : '-';
  }
  if (isDurationUnit(unit)) {
    let seconds = Number(value);
    return Number.isFinite(seconds) ? formatDurationSeconds(seconds) : '-';
  }
  let preparedValue = prepareChartValue(value, unit);
  return preparedValue === null ? '-' : formatAnalyticsValue(preparedValue, unit);
}

function getSummaryItemValue(rawValue, unit) {
  if (isDurationUnit(unit)) {
    let seconds = Number(rawValue);
    return Number.isFinite(seconds) ? seconds : null;
  }
  return prepareChartValue(rawValue, unit);
}

function setDefaultState(where) {
  if (socket.readyState === WebSocket.OPEN) {
    sendShowWhere(where);
  }
}
// sendShowWhere(where);

setDefaultState(where);
headerAnalyticsController(analytics);
filterDate(where);

function handlerAnalyticsOnPage(data, user) {
  analyticsDealerDrilldownContext = null;
  analyticsWhoseModeBeforeDrilldown = null;
  analyticsOnPage(data);
}

function handlerAnalyticsDealerDependentsOnPage(data, user) {
  if (data && data.error) {
    renderAnalyticsMessage(document.getElementById('analytics'), 'Не удалось загрузить контрагентов дилера');
    return;
  }
  if (data && data.dealerDependentsContext) {
    analyticsDealerDrilldownContext = data.dealerDependentsContext;
  }
  analyticsWhoseModeBeforeDrilldown = analyticsWhoseMode;
  analyticsWhoseMode = 'dealer_dependents';
  analyticsOnPage(data, { dealerDependents: true });
}

function handlerAnalyticsUserSearch(data, user) {
  analyticsUserSearch(data);
}

function analyticsOnPage(data, options = {}) {
  let analyticsBlock = document.getElementById('analytics');
  if (!analyticsBlock) {
    return;
  }

  if (!options.dealerDependents && !options.restoreDealer && analyticsWhoseMode === 'dealer' && data) {
    analyticsDealerDrilldownParentSnapshot = JSON.parse(JSON.stringify(data));
  }

  data = packageAnalyticsUsers(data);
  data = removeAnalyticsUserPackages(data);
  destroyAnalyticsCharts();
  analyticsBlock.innerHTML = '';

  if (typeof Chart === 'undefined') {
    renderAnalyticsMessage(analyticsBlock, 'Chart.js не загружен');
    return;
  }

  if (options.dealerDependents || data.dealerDependentsContext) {
    renderDealerDependentsDashboard(analyticsBlock, data);
    return;
  }

  let chartConfigs = getAnalyticsChartConfigs(data);
  if (chartConfigs.length === 0) {
    renderAnalyticsMessage(analyticsBlock, 'Нет данных для отображения');
    return;
  }

  let dashboard = createTagHtml('div', 'analytics_dashboard');
  analyticsBlock.appendChild(dashboard);

  let tableConfigs = chartConfigs.filter(config => config.kind === 'table');
  chartConfigs.filter(config => config.kind !== 'table').forEach(config => {
    renderAnalyticsChart(dashboard, config);
  });
  if (tableConfigs.length > 0) {
    renderAnalyticsTableSwitcher(dashboard, tableConfigs);
  }
}

function packageAnalyticsUsers(data) {
  if (!data || typeof data !== 'object') {
    return data;
  }

  let usersPackage = getAnalyticsUsersPackage(data);
  let userIndex = buildAnalyticsUserIndex(usersPackage);
  analyticsUsersCatalog = userIndex;
  if (Object.keys(userIndex).length > 0) {
    Object.keys(data).forEach(metricKey => {
      if (metricKey === 'analyticsUsers' || metricKey === 'dealerDependentsByDealer' || metricKey === 'dealerDependentsContext') {
        return;
      }
      data[metricKey] = filterAnalyticsMetricByCatalog(data[metricKey], userIndex);
    });
  }
  Object.keys(data).forEach(metricKey => {
    if (metricKey === 'analyticsUsers' || metricKey === 'dealerDependentsByDealer' || metricKey === 'dealerDependentsContext') {
      return;
    }
    packageAnalyticsRowsUsers(data[metricKey], userIndex);
  });
  appendUserProportions(data);
  return data;
}

function isAnalyticsMetricDataRow(row) {
  if (!row || typeof row !== 'object' || Array.isArray(row)) {
    return false;
  }
  return Boolean(row.idUser || row.idManager || row.idContractor || row.idDealer || row.idUserDealer);
}

function isAnalyticsRowInCatalog(row, userIndex) {
  let userKeys = ['idUser', 'idManager', 'idContractor', 'idDealer', 'idUserDealer'];
  for (let i = 0; i < userKeys.length; i++) {
    let userKey = userKeys[i];
    if (!row[userKey]) {
      continue;
    }
    let idUser = Number(row[userKey]);
    if (!Number.isFinite(idUser) || idUser <= 0) {
      return false;
    }
    return Boolean(lookupAnalyticsUser(userIndex, idUser));
  }
  return true;
}

function filterAnalyticsMetricByCatalog(rawData, userIndex) {
  if (!rawData || typeof rawData !== 'object') {
    return rawData;
  }
  if (!userIndex || Object.keys(userIndex).length === 0) {
    return rawData;
  }

  if (isAnalyticsMetricDataRow(rawData)) {
    return isAnalyticsRowInCatalog(rawData, userIndex) ? rawData : null;
  }

  if (Array.isArray(rawData)) {
    let filtered = rawData
      .map(item => filterAnalyticsMetricByCatalog(item, userIndex))
      .filter(item => item !== null);
    return filtered;
  }

  let result = {};
  Object.keys(rawData).forEach(key => {
    let filtered = filterAnalyticsMetricByCatalog(rawData[key], userIndex);
    if (filtered !== null) {
      result[key] = filtered;
    }
  });
  return result;
}

function getAnalyticsUsersPackage(data) {
  let usersPackage = data.analyticsUsers;
  if (!isAnalyticsUsersPackage(usersPackage)) {
    return null;
  }
  return usersPackage;
}

function isAnalyticsUsersPackage(usersPackage) {
  if (!usersPackage || typeof usersPackage !== 'object' || Array.isArray(usersPackage)) {
    return false;
  }
  return !(
    usersPackage.timeFirstResponse50 ||
    usersPackage.timeManagerFirstResponse50 ||
    usersPackage.dataDialogs ||
    usersPackage.timeResponse50
  );
}

function packageAnalyticsRowsUsers(rawRows, userIndex) {
  let rows = normalizeAnalyticsRows(rawRows);
  rows.forEach(row => {
    attachAnalyticsRowUser(row, userIndex);
    if (Array.isArray(row.openedDialogsByType)) {
      row.openedDialogsByType.forEach(typeRow => attachAnalyticsRowUser(typeRow, userIndex, row));
    }
  });
}

function attachAnalyticsRowUser(row, userIndex, sourceRow) {
  if (!row || typeof row !== 'object' || isAnalyticsUserProfile(row)) {
    return;
  }

  let idUser = getAnalyticsRowUserId(row) || (sourceRow ? getAnalyticsRowUserId(sourceRow) : null);
  if (!idUser) {
    return;
  }

  row.userId = idUser;
  if (!row.user) {
    row.user = lookupAnalyticsUser(userIndex, idUser);
  }
  if (!row.user && sourceRow && sourceRow.user) {
    row.user = sourceRow.user;
  }
}

function isAnalyticsUserProfile(row) {
  if (!row || typeof row !== 'object') {
    return false;
  }
  if (row.p50 !== undefined || row.p90 !== undefined || row.p95 !== undefined) {
    return false;
  }
  if (row.openedDialogs !== undefined || row.activeDialogs !== undefined || row.typeDialog !== undefined) {
    return false;
  }
  if (row.turnsCount !== undefined || row.ordersCount !== undefined) {
    return false;
  }
  if (row.ordersPerDialog !== undefined || row.ordersPerTurn !== undefined || row.complaintsPerOrder !== undefined) {
    return false;
  }
  if (row.maxTimeResponse !== undefined || row.dialogDurationP50 !== undefined) {
    return false;
  }
  return Boolean(row.name || row.displayName || row.search || row.role);
}

function lookupAnalyticsUser(userIndex, idUser) {
  if (!userIndex || idUser === undefined || idUser === null || idUser === '') {
    return null;
  }
  return userIndex[idUser]
    || userIndex[String(idUser)]
    || userIndex[Number(idUser)]
    || null;
}

function registerAnalyticsUser(index, user, fallbackId) {
  if (!index || !user || typeof user !== 'object') {
    return;
  }

  let idUser = user.idUser || user.userId || user.id || fallbackId || null;
  if (!idUser) {
    return;
  }

  index[idUser] = user;
  index[String(idUser)] = user;
  index[Number(idUser)] = user;
}

function indexAnalyticsUsersPackage(index, usersPackage) {
  if (!usersPackage || typeof usersPackage !== 'object') {
    return;
  }

  if (Array.isArray(usersPackage)) {
    usersPackage.forEach(user => registerAnalyticsUser(index, user));
    return;
  }

  Object.keys(usersPackage).forEach(key => {
    let entry = usersPackage[key];
    if (!entry || typeof entry !== 'object' || Array.isArray(entry)) {
      return;
    }
    let fallbackId = /^\d+$/.test(String(key)) ? Number(key) : key;
    registerAnalyticsUser(index, entry, fallbackId);
  });
}

function getAnalyticsRowUserId(row) {
  return row.idUser || row.idManager || row.idContractor || row.idDealer || row.idUserDealer || null;
}

function buildAnalyticsUserIndex(usersPackage) {
  let index = {};
  indexAnalyticsUsersPackage(index, usersPackage);
  collectAnalyticsUsers(index, usersPackage);
  return index;
}

function collectAnalyticsUsers(index, users) {
  if (!users) {
    return;
  }

  if (Array.isArray(users)) {
    users.forEach(item => collectAnalyticsUsers(index, item));
    return;
  }

  if (typeof users !== 'object') {
    return;
  }

  if (users.users) {
    collectAnalyticsUsers(index, users.users);
  }

  if (users.idUser || users.userId || users.id) {
    registerAnalyticsUser(index, users);
    if (users.idDealer) {
      index[users.idDealer] = users;
      index[String(users.idDealer)] = users;
      index[Number(users.idDealer)] = users;
    }
    return;
  }

  Object.keys(users).forEach(key => {
    let entry = users[key];
    if (entry && typeof entry === 'object' && !Array.isArray(entry)) {
      let fallbackId = /^\d+$/.test(String(key)) ? Number(key) : null;
      registerAnalyticsUser(index, entry, fallbackId);
    }
    collectAnalyticsUsers(index, entry);
  });
}

function removeAnalyticsUserPackages(data) {
  if (!data || typeof data !== 'object') {
    return data;
  }
  delete data.analyticsUsers;
  return data;
}

function destroyAnalyticsCharts() {
  analyticsCharts.forEach(chart => chart.destroy());
  analyticsCharts = [];
  analyticsChartStates = [];
}

function renderAnalyticsMessage(parent, message) {
  let messageBlock = createTagHtml('div', 'analytics_message');
  messageBlock.textContent = message;
  parent.appendChild(messageBlock);
}

function isContractorDealerView() {
  return analyticsWhoseMode === 'contractor'
    || analyticsWhoseMode === 'dealer'
    || analyticsWhoseMode === 'dealer_dependents';
}

function collectContractorDealerMetricSpecs(data) {
  let specs = [];
  let seenColumnIds = new Set();

  function addSpec(spec) {
    if (seenColumnIds.has(spec.columnId)) {
      return;
    }
    seenColumnIds.add(spec.columnId);
    specs.push(spec);
  }

  function collectSpecsFromGroups(groups) {
    groups.forEach(group => {
      group.fields.forEach(field => {
        if (data[field.sourceKey] === undefined || data[field.sourceKey] === null) {
          return;
        }
        addSpec({
          sourceKey: field.sourceKey,
          valueKey: field.valueKey,
          label: group.title + ' ' + field.label,
          unit: group.unit,
          columnId: field.sourceKey + '_' + field.valueKey
        });
      });
    });
  }

  collectSpecsFromGroups(analyticsSlaMetricGroups);
  collectSpecsFromGroups(analyticsScalarMetricGroups);



  if (data.dataDialogs !== undefined && data.dataDialogs !== null) {
    analyticsDataDialogsMetricConfigs.forEach(config => {
      config.fields.forEach(field => {
        addSpec({
          sourceKey: 'dataDialogs',
          valueKey: field.key,
          label: config.splitFields && config.fields.length > 1
            ? config.title + ' - ' + field.label
            : config.title,
          unit: config.unit,
          columnId: 'dataDialogs_' + field.key
        });
      });
    });

    if (data.dataOrders && data.dataTurns) {
      addSpec({
        sourceKey: 'dataDialogs',
        valueKey: 'ordersPerDialog',
        label: 'Заказы / Диалоги',
        unit: 'ratio',
        columnId: 'dataDialogs_ordersPerDialog'
      });
      addSpec({
        sourceKey: 'dataDialogs',
        valueKey: 'ordersPerTurn',
        label: 'Заказы / Сообщения',
        unit: 'ratio',
        columnId: 'dataDialogs_ordersPerTurn'
      });
      addSpec({
        sourceKey: 'dataDialogs',
        valueKey: 'complaintsPerOrder',
        label: 'Рекламации / Заказы',
        unit: 'ratio',
        columnId: 'dataDialogs_complaintsPerOrder'
      });
    }
  }

  return specs;
}

function getAnalyticsUserPeriodKey(row) {
  let userId = getAnalyticsMetricUserId(row);
  if (!userId) {
    return '';
  }
  return String(userId) + '|' + (row.period || 'all');
}

function getAnalyticsMetricUserId(row) {
  if (analyticsWhoseMode === 'dealer') {
    return row.idUserDealer || row.idDealer || row.idUser || row.idContractor || null;
  }
  if (analyticsWhoseMode === 'dealer_dependents') {
    return getAnalyticsRowUserId(row);
  }
  return getAnalyticsRowUserId(row);
}

function buildMetricIndex(rows, valueKey) {
  let index = {};
  normalizeAnalyticsRows(rows).forEach(row => {
    let key = getAnalyticsUserPeriodKey(row);
    if (!key) {
      return;
    }
    index[key] = (index[key] || 0) + (Number(row[valueKey]) || 0);
  });
  return index;
}

function buildOrdersIndexByUser(data) {
  let usersPackage = getAnalyticsUsersPackage(data);
  if (!usersPackage || !data.dataOrders) {
    return {};
  }

  let ordersByPointPeriod = {};
  normalizeAnalyticsRows(data.dataOrders).forEach(row => {
    if (!row.pointId) {
      return;
    }
    let period = row.period || 'all';
    let key = row.pointId + '|' + period;
    ordersByPointPeriod[key] = (ordersByPointPeriod[key] || 0) + (Number(row.ordersCount) || 0);
  });

  let index = {};
  let seenUserIds = new Set();
  Object.keys(usersPackage).forEach(userKey => {
    let user = usersPackage[userKey];
    if (!user || typeof user !== 'object' || !user.id || seenUserIds.has(user.id)) {
      return;
    }
    seenUserIds.add(user.id);

    let idPoints = Array.isArray(user.idPoint) ? user.idPoint : [];
    if (idPoints.length === 0) {
      return;
    }

    let periods = new Set(['all']);
    normalizeAnalyticsRows(data.dataOrders).forEach(row => {
      if (idPoints.includes(Number(row.pointId))) {
        periods.add(row.period || 'all');
      }
    });
    normalizeAnalyticsRows(data.dataDialogs).forEach(row => {
      if (getAnalyticsMetricUserId(row) == user.id) {
        periods.add(row.period || 'all');
      }
    });
    normalizeAnalyticsRows(data.dataTurns).forEach(row => {
      if (getAnalyticsMetricUserId(row) == user.id) {
        periods.add(row.period || 'all');
      }
    });

    periods.forEach(period => {
      let ordersCount = idPoints.reduce((sum, pointId) => {
        return sum + (ordersByPointPeriod[pointId + '|' + period] || 0);
      }, 0);
      index[String(user.id) + '|' + period] = ordersCount;
    });
  });

  return index;
}

function buildComplaintsIndexByUser(data) {
  let index = {};
  normalizeAnalyticsRows(data.dataDialogs).forEach(row => {
    let key = getAnalyticsUserPeriodKey(row);
    if (!key || !Array.isArray(row.openedDialogsByType)) {
      return;
    }
    row.openedDialogsByType.forEach(typeRow => {
      if (getDialogTypeInfo(typeRow.typeDialog).key !== 'complaint') {
        return;
      }
      index[key] = (index[key] || 0) + (Number(typeRow.openedDialogs) || 0);
    });
  });
  return index;
}

function appendUserProportions(data) {
  if (!isContractorDealerView() || !data || !data.dataDialogs || !data.dataOrders || !data.dataTurns) {
    return data;
  }

  let ordersIndex = buildOrdersIndexByUser(data);
  let turnsIndex = buildMetricIndex(data.dataTurns, 'turnsCount');
  let complaintsIndex = buildComplaintsIndexByUser(data);

  normalizeAnalyticsRows(data.dataDialogs).forEach(row => {
    let key = getAnalyticsUserPeriodKey(row);
    if (!key) {
      return;
    }

    let orders = ordersIndex[key] || 0;
    let dialogs = Number(row.openedDialogs) || 0;
    let turns = turnsIndex[key] || 0;
    let complaints = complaintsIndex[key] || 0;
    row.ordersPerDialog = dialogs > 0 ? orders / dialogs : null;
    row.ordersPerTurn = turns > 0 ? orders / turns : null;
    row.complaintsPerOrder = orders > 0 ? complaints / orders : null;
  });

  return data;
}

function buildUnifiedContractorDealerTableData(data) {
  let specs = collectContractorDealerMetricSpecs(data);
  let groupedRows = {};
  let periodLabels = new Set();
  let dialogTypeColumnIds = new Set();

  function ensureGroupedRow(row, index) {
    let rowKey = getContractorTableKey(row, index);
    if (!groupedRows[rowKey]) {
      groupedRows[rowKey] = {
        __contractorKey: rowKey,
        contractor: getUserLabel(row),
        details: getUserDetails(row, index)
      };
    }
    return groupedRows[rowKey];
  }

  function setTableCell(tableRow, columnId, value, unit, period) {
    if (period) {
      periodLabels.add(period);
    }
    let columnKey = period ? getPeriodColumnKey(period, columnId) : columnId;
    tableRow[columnKey] = formatMetricCellValue(value, unit);
  }

  function mergeDialogTypeAmount(tableRow, typeDialog, amount, period) {
    let typeInfo = getDialogTypeInfo(typeDialog);
    let columnId = 'dialogType_' + typeInfo.key;
    dialogTypeColumnIds.add(columnId);
    let columnKey = period ? getPeriodColumnKey(period, columnId) : columnId;
    tableRow[columnKey] = (Number(tableRow[columnKey]) || 0) + amount;
  }

  specs.forEach(spec => {
    let rows = normalizeAnalyticsRows(data[spec.sourceKey]);
    rows.forEach((row, index) => {
      let tableRow = ensureGroupedRow(row, index);
      let period = row.period && row.period !== 'all' ? getPeriodLabel(row) : null;
      setTableCell(tableRow, spec.columnId, row[spec.valueKey], spec.unit, period);
    });
  });

  normalizeAnalyticsRows(data.dataDialogs).forEach((row, index) => {
    if (!Array.isArray(row.openedDialogsByType)) {
      return;
    }
    let tableRow = ensureGroupedRow(row, index);
    let period = row.period && row.period !== 'all' ? getPeriodLabel(row) : null;
    row.openedDialogsByType.forEach(typeRow => {
      mergeDialogTypeAmount(tableRow, typeRow.typeDialog, Number(typeRow.openedDialogs) || 0, period);
    });
  });

  if (specs.length === 0 && Object.keys(groupedRows).length === 0) {
    return null;
  }

  let usePeriodColumns = periodLabels.size > 0;
  let periods = usePeriodColumns ? uniqueValues([...periodLabels]) : [];
  let parameters = buildContractorDealerParameters(specs, dialogTypeColumnIds, usePeriodColumns, periods);
  let columns = [
    { key: 'contractor', label: getAnalyticsUserColumnLabel() },
    { key: 'details', label: 'Детали' }
  ];

  if (usePeriodColumns) {
    periods.forEach(period => {
      specs.forEach(spec => {
        columns.push({
          key: getPeriodColumnKey(period, spec.columnId),
          label: period + ' - ' + spec.label,
          unit: spec.unit
        });
      });
      dialogTypeColumnIds.forEach(columnId => {
        columns.push({
          key: getPeriodColumnKey(period, columnId),
          label: period + ' - ' + getDialogTypeInfo(columnId.replace('dialogType_', '')).label,
          unit: 'count'
        });
      });
    });
  } else {
    specs.forEach(spec => {
      columns.push({
        key: spec.columnId,
        label: spec.label,
        unit: spec.unit
      });
    });
    dialogTypeColumnIds.forEach(columnId => {
      columns.push({
        key: columnId,
        label: getDialogTypeInfo(columnId.replace('dialogType_', '')).label,
        unit: 'count'
      });
    });
  }

  return {
    columns: columns,
    rows: Object.values(groupedRows),
    parameters: parameters
  };
}

function buildContractorDealerParameters(specs, dialogTypeColumnIds, usePeriodColumns, periods) {
  let parameters = [];

  specs.forEach(spec => {
    parameters.push({
      id: spec.columnId,
      label: spec.label,
      unit: spec.unit,
      columns: usePeriodColumns
        ? periods.map(period => ({
          key: getPeriodColumnKey(period, spec.columnId),
          label: period
        }))
        : [{ key: spec.columnId, label: spec.label }]
    });
  });

  dialogTypeColumnIds.forEach(columnId => {
    let typeLabel = getDialogTypeInfo(columnId.replace('dialogType_', '')).label;
    parameters.push({
      id: columnId,
      label: typeLabel,
      unit: 'count',
      columns: usePeriodColumns
        ? periods.map(period => ({
          key: getPeriodColumnKey(period, columnId),
          label: period
        }))
        : [{ key: columnId, label: typeLabel }]
    });
  });

  return parameters;
}

function getContractorDealerSourceRows(data) {
  let dialogRows = normalizeAnalyticsRows(data.dataDialogs);
  if (dialogRows.length > 0) {
    return dialogRows;
  }

  for (let group of analyticsSlaMetricGroups.concat(analyticsScalarMetricGroups)) {
    for (let field of group.fields) {
      let rows = normalizeAnalyticsRows(data[field.sourceKey]);
      if (rows.length > 0) {
        return rows;
      }
    }
  }

  return [];
}

function buildContractorDealerTableConfigs(data) {
  let tableData = buildUnifiedContractorDealerTableData(data);
  if (!tableData || tableData.rows.length === 0) {
    return [];
  }

  return [{
    kind: 'table',
    title: getAnalyticsUsersTableTitle(),
    data: {
      columns: tableData.columns,
      rows: tableData.rows
    },
    parameters: tableData.parameters,
    sourceRows: getContractorDealerSourceRows(data),
    rawData: data,
    dealerDependentsByDealer: data.dealerDependentsByDealer || {},
    unit: 'mixed',
    isUnifiedContractorTable: true
  }];
}

function hasContractorDealerPeriodSplit(config) {
  if (!config || !config.data || !Array.isArray(config.data.columns)) {
    return false;
  }
  return config.data.columns.some(column => String(column.key).indexOf('period_') === 0);
}

function filterAnalyticsDataByContractorKeys(data, contractorKeys) {
  if (!data || contractorKeys.size === 0) {
    return data;
  }

  let result = Object.assign({}, data);
  Object.keys(data).forEach(metricKey => {
    if (metricKey === 'analyticsUsers') {
      return;
    }

    let rows = normalizeAnalyticsRows(data[metricKey]);
    if (rows.length === 0) {
      return;
    }

    let filteredRows = rows.filter((row, index) => contractorKeys.has(getContractorTableKey(row, index)));
    if (Array.isArray(data[metricKey])) {
      result[metricKey] = filteredRows;
      return;
    }
    if (filteredRows.length === 1) {
      result[metricKey] = filteredRows[0];
      return;
    }
    if (filteredRows.length === 0) {
      result[metricKey] = null;
      return;
    }
    result[metricKey] = filteredRows;
  });

  return result;
}

function shouldRenderContractorDealerSelectedAsSummaries(config, contractorKeys) {
  return !hasContractorDealerPeriodSplit(config) && contractorKeys.size === 1;
}

function renderContractorDealerSelectedSummaries(parent, config, contractorKeys) {
  if (!shouldRenderContractorDealerSelectedAsSummaries(config, contractorKeys)) {
    renderContractorDealerMetricCharts(parent, config, contractorKeys, ' - выбранные');
    return;
  }

  let selectedRows = config.data.rows.filter(row => contractorKeys.has(row.__contractorKey));
  if (selectedRows.length === 0) {
    let message = createTagHtml('div', 'analytics_message');
    message.textContent = 'Нет данных для отображения';
    parent.appendChild(message);
    return;
  }

  if (!config.rawData) {
    renderContractorDealerMetricCharts(parent, config, contractorKeys, ' - выбранные');
    return;
  }

  let summariesHost = createTagHtml('div', 'analytics_contractor_summaries');
  let keySet = new Set([selectedRows[0].__contractorKey]);
  let filteredData = filterAnalyticsDataByContractorKeys(config.rawData, keySet);
  buildFactoryStyleChartConfigs(filteredData).forEach(summaryConfig => {
    renderAnalyticsChart(summariesHost, summaryConfig);
  });
  parent.appendChild(summariesHost);
}

function buildFactoryStyleChartConfigs(data) {
  let configs = [];
  let handledMetricKeys = new Set([
    'analyticsUsers',
    'dataDialogs',
    'dataTurns',
    'dataOrders'
  ]);

  addGroupedScalarMetricConfigs(configs, data, handledMetricKeys, analyticsSlaMetricGroups);
  addGroupedScalarMetricConfigs(configs, data, handledMetricKeys);
  addRawAnalyticsMetricConfigs(configs, data, handledMetricKeys);
  processMetricConfigs(configs, data, analyticsDataDialogsMetricConfigs, handledMetricKeys);

  if (isContractorDealerView()) {
    addDialogTypeConfig(
      configs,
      buildOpenedDialogTypeRows(data.dataDialogs),
      'Открытые диалоги по типам'
    );
    return configs;
  }

  addDialogTypeConfig(
    configs,
    buildOpenedDialogTypeRows(data.dataDialogs),
    'Открытые диалоги по типам'
  );

  return configs;
}

function getAnalyticsChartConfigs(data) {
  if (!data || typeof data !== 'object') {
    return [];
  }

  if (isContractorDealerView()) {
    return buildContractorDealerTableConfigs(data);
  }

  return buildFactoryStyleChartConfigs(data);
}


const analyticsDataDialogsMetricConfigs = [
    {
      key: 'dataDialogs',
      title: 'Открытые диалоги',
      unit: 'count',
      userTable: true,
      fields: [{ key: 'openedDialogs', label: 'Открытые', colorIndex: 0 }]
    },
    {
      key: 'dataDialogs',
      title: 'Активные диалоги',
      unit: 'count',
      userTable: true,
      fields: [{ key: 'activeDialogs', label: 'Активные', colorIndex: 2 }]
    },
    {
      key: 'dataDialogs',
      title: 'Длительность диалога',
      unit: 'hours',
      userTable: true,
      splitFields: true,
      fields: [
        { key: 'dialogDurationP50', label: 'p50', colorIndex: 0 },
        { key: 'dialogDurationP90', label: 'p90', colorIndex: 1 },
        { key: 'dialogDurationP95', label: 'p95', colorIndex: 3 }
      ]
    },
    {
      key: 'dataDialogs',
      title: 'Сообщений в диалоге',
      unit: 'count',
      userTable: true,
      splitFields: true,
      fields: [
        { key: 'messagesCountP50', label: 'p50', colorIndex: 0 },
        { key: 'messagesCountP90', label: 'p90', colorIndex: 1 },
        { key: 'messagesCountP95', label: 'p95', colorIndex: 3 }
      ]
    }
];

function processMetricConfigs(configs, data, metricConfigs, handledMetricKeys) {
  metricConfigs.forEach(metricConfig => {
    let rows = normalizeAnalyticsRows(data[metricConfig.key]);
    if (rows.length === 0) {
      return;
    }
    if (metricConfig.key) {
      handledMetricKeys.add(metricConfig.key);
    }
    if (shouldRenderMetricAsSummary(rows)) {
      let summaryData = buildSummaryMetricData(rows[0], metricConfig.fields, metricConfig.unit);
      if (summaryData.items.length > 0) {
        configs.push({
          kind: 'summary',
          title: metricConfig.title,
          data: summaryData,
          unit: metricConfig.unit
        });
      }
      return;
    }
    if (isUserTableMetricConfig(metricConfig)) {
      if (isSingleUserPeriodRows(rows)) {
        pushMetricChartConfigs(configs, rows, metricConfig);
        return;
      }
      let tableData = buildMetricTableData(rows, metricConfig.fields, metricConfig.unit);
      if (tableData.rows.length > 0) {
        configs.push({
          kind: 'table',
          title: metricConfig.title,
          data: tableData,
          sourceRows: rows,
          fields: metricConfig.fields,
          unit: metricConfig.unit
        });
      }
      return;
    }
    pushMetricChartConfigs(configs, rows, metricConfig);
  });
}

function pushMetricChartConfigs(configs, rows, metricConfig) {
  getChartMetricFields(metricConfig).forEach(fieldGroup => {
    let chartData = buildMetricChartData(rows, fieldGroup.fields, metricConfig.unit);
    if (chartData.datasets.length > 0) {
      configs.push({
        title: fieldGroup.title,
        type: getMetricChartType(rows),
        data: chartData,
        unit: metricConfig.unit,
        canToggleLabels: hasUser(rows)
      });
    }
  });
}

function addScalarMetricChartConfig(configs, rows, title, valueKey, unit) {
  if (shouldRenderMetricAsSummary(rows)) {
    let value = getSummaryItemValue(rows[0][valueKey], unit);
    if (value !== null) {
      configs.push({
        kind: 'summary',
        title: title,
        data: {
          items: [{
            label: getAnalyticsColumnLabel(valueKey),
            value: value
          }]
        },
        unit: unit
      });
    }
    return;
  }

  let chartData = buildMetricChartData(
    rows,
    [{ key: valueKey, label: getAnalyticsColumnLabel(valueKey) }],
    unit
  );
  if (chartData.datasets.length > 0) {
    configs.push({
      title: title,
      type: getMetricChartType(rows),
      data: chartData,
      unit: unit,
      canToggleLabels: hasUser(rows)
    });
  }
}

const analyticsSlaMetricGroups = [
  {
    title: 'Первый ответ менеджера',
    unit: 'percent',
    fields: [
      { sourceKey: 'timeFirstResponseSla', valueKey: 'slaPercent', label: 'SLA' },
      { sourceKey: 'timeManagerFirstResponseSla', valueKey: 'slaPercent', label: 'SLA' }
    ]
  },
  {
    title: 'Последующие ответы менеджера',
    unit: 'percent',
    fields: [
      { sourceKey: 'timeResponseSla', valueKey: 'slaPercent', label: 'SLA' },
      { sourceKey: 'timeManagerResponseSla', valueKey: 'slaPercent', label: 'SLA' }
    ]
  }
];

const analyticsScalarMetricGroups = [
  {
    title: 'Первый ответ менеджера',
    unit: 'hours',
    fields: [
      { sourceKey: 'timeFirstResponse50', valueKey: 'p50', label: 'p50' },
      { sourceKey: 'timeFirstResponse90', valueKey: 'p90', label: 'p90' },
      { sourceKey: 'timeFirstResponse95', valueKey: 'p95', label: 'p95' },
      { sourceKey: 'maxTimeFirstResponse', valueKey: 'maxTimeResponse', label: 'Максимум' }
    ]
  },
  {
    title: 'Последующие ответы менеджера',
    unit: 'hours',
    fields: [
      { sourceKey: 'timeResponse50', valueKey: 'p50', label: 'p50' },
      { sourceKey: 'timeResponse90', valueKey: 'p90', label: 'p90' },
      { sourceKey: 'timeResponse95', valueKey: 'p95', label: 'p95' }
    ]
  },
  {
    title: 'Время до ответа контрагента',
    unit: 'hours',
    fields: [
      { sourceKey: 'timeBeforeNextTurn50', valueKey: 'p50', label: 'p50' },
      { sourceKey: 'timeBeforeNextTurn90', valueKey: 'p90', label: 'p90' },
      { sourceKey: 'timeBeforeNextTurn95', valueKey: 'p95', label: 'p95' }
    ]
  },
  {
    title: 'Первый ответ менеджера',
    unit: 'hours',
    fields: [
      { sourceKey: 'timeManagerFirstResponse50', valueKey: 'p50', label: 'p50' },
      { sourceKey: 'timeManagerFirstResponse90', valueKey: 'p90', label: 'p90' },
      { sourceKey: 'timeManagerFirstResponse95', valueKey: 'p95', label: 'p95' },
      { sourceKey: 'maxTimeManagerFirstResponse', valueKey: 'maxTimeResponse', label: 'Максимум' }
    ]
  },
  {
    title: 'Последующие ответы менеджера',
    unit: 'hours',
    fields: [
      { sourceKey: 'timeManagerResponse50', valueKey: 'p50', label: 'p50' },
      { sourceKey: 'timeManagerResponse90', valueKey: 'p90', label: 'p90' },
      { sourceKey: 'timeManagerResponse95', valueKey: 'p95', label: 'p95' }
    ]
  },
  {
    title: 'Ответы контрагента',
    unit: 'hours',
    fields: [
      { sourceKey: 'timeContractorFirstResponse50', valueKey: 'p50', label: 'p50' },
      { sourceKey: 'timeContractorFirstResponse90', valueKey: 'p90', label: 'p90' },
      { sourceKey: 'timeContractorFirstResponse95', valueKey: 'p95', label: 'p95' }
    ]
  }
];

function addGroupedScalarMetricConfigs(configs, data, handledMetricKeys, groups) {
  (groups || analyticsScalarMetricGroups).forEach(group => {
    let items = [];
    let presentFields = group.fields.filter(field => data[field.sourceKey] !== undefined && data[field.sourceKey] !== null);
    if (presentFields.length === 0) {
      return;
    }

    let canGroup = presentFields.every(field => {
      let rows = normalizeAnalyticsRows(data[field.sourceKey]);
      return rows.length === 0 || shouldRenderMetricAsSummary(rows);
    });
    if (!canGroup) {
      presentFields.forEach(field => {
        let rows = normalizeAnalyticsRows(data[field.sourceKey]);
        if (rows.length === 0) {
          return;
        }
        handledMetricKeys.add(field.sourceKey);
        addScalarMetricChartConfig(
          configs,
          rows,
          group.title + ' - ' + field.label,
          field.valueKey,
          group.unit
        );
      });
      return;
    }

    presentFields.forEach(field => {
      let rows = normalizeAnalyticsRows(data[field.sourceKey]);
      if (rows.length === 0) {
        return;
      }
      let value = getSummaryItemValue(rows[0][field.valueKey], group.unit);
      if (value !== null) {
        items.push({ label: field.label, value: value });
      }
      handledMetricKeys.add(field.sourceKey);
    });

    if (items.length > 0) {
      configs.push({
        kind: 'summary',
        title: group.title,
        data: { items: items },
        unit: group.unit
      });
    }
  });
}

function isUserTableMetricConfig(metricConfig) {
  if (metricConfig.key === 'dataDialogs' && analyticsWhoseMode === 'factory') {
    return false;
  }
  return Boolean(metricConfig.userTable);
}

function buildOpenedDialogTypeRows(rawRows) {
  let rows = normalizeAnalyticsRows(rawRows);
  let result = [];

  rows.forEach(row => {
    if (!Array.isArray(row.openedDialogsByType)) {
      return;
    }

    row.openedDialogsByType.forEach(typeRow => {
      result.push({
        period: row.period,
        user: row.user,
        userId: row.userId,
        typeDialog: typeRow.typeDialog,
        total: typeRow.openedDialogs
      });
    });
  });

  return result;
}

function addRawAnalyticsMetricConfigs(configs, data, handledMetricKeys) {
  Object.keys(data).forEach(metricKey => {
    if (handledMetricKeys.has(metricKey)) {
      return;
    }

    let rows = normalizeAnalyticsRows(data[metricKey]);
    if (rows.length === 0) {
      return;
    }

    if (shouldRenderMetricAsSummary(rows)) {
      let summaryData = buildRawSummaryMetricData(rows, metricKey);
      if (summaryData.items.length > 0) {
        configs.push({
          kind: 'summary',
          title: getAnalyticsMetricTitle(metricKey),
          data: summaryData,
          unit: detectAnalyticsMetricUnit(metricKey, rows[0])
        });
      }
      handledMetricKeys.add(metricKey);
      return;
    }

    let valueKeys = getAnalyticsRowValueKeys(rows[0]);
    if (valueKeys.length === 0) {
      return;
    }

    let chartsAdded = 0;
    valueKeys.forEach(valueKey => {
      let unit = detectAnalyticsMetricUnit(metricKey, rows[0], valueKey);
      let title = valueKeys.length === 1
        ? getAnalyticsMetricTitle(metricKey)
        : getAnalyticsMetricTitle(metricKey) + ' - ' + getAnalyticsColumnLabel(valueKey);
      let beforeCount = configs.length;
      addScalarMetricChartConfig(configs, rows, title, valueKey, unit);
      if (configs.length > beforeCount) {
        chartsAdded += 1;
      }
    });

    if (chartsAdded > 0) {
      handledMetricKeys.add(metricKey);
      return;
    }

    let tableData = buildRawAnalyticsTableData(rows);
    if (tableData.rows.length === 0) {
      return;
    }

    configs.push({
      kind: 'table',
      title: getAnalyticsMetricTitle(metricKey),
      data: tableData,
      sourceRows: rows,
      fields: tableData.columns.filter(column => !['period', 'contractor', 'details'].includes(column.key)),
      unit: 'raw'
    });
    handledMetricKeys.add(metricKey);
  });
}

function buildRawSummaryMetricData(rows, metricKey) {
  let row = rows[0];
  let unit = detectAnalyticsMetricUnit(metricKey, row);
  let items = getAnalyticsRowValueKeys(row).map(key => {
    return {
      label: getAnalyticsColumnLabel(key),
      value: getSummaryItemValue(row[key], unit)
    };
  }).filter(item => item.value !== null);

  return { items: items };
}

function getAnalyticsRowValueKeys(row) {
  if (!row || typeof row !== 'object') {
    return [];
  }

  return Object.keys(row).filter(key => {
    if (['user', 'userId', 'idUser', 'idManager', 'idContractor', 'idDealer', 'idUserDealer', 'period'].includes(key)) {
      return false;
    }
    if (row[key] && typeof row[key] === 'object') {
      return false;
    }
    return row[key] !== undefined && row[key] !== null && row[key] !== '';
  });
}

function detectAnalyticsMetricUnit(metricKey, row, valueKey) {
  let key = valueKey || metricKey || '';
  if (/time|duration|response/i.test(key) && !/dialogs|messages/i.test(key)) {
    return 'hours';
  }
  if (/dialogs|messages|count/i.test(key)) {
    return 'count';
  }
  if (/sla/i.test(key)) {
    return 'percent';
  }
  if (row) {
    let rowKeys = getAnalyticsRowValueKeys(row);
    if (rowKeys.some(item => /^p\d+$/.test(item) || item === 'maxTimeResponse')) {
      return 'hours';
    }
  }
  return 'raw';
}

function buildRawAnalyticsTableData(rows) {
  let scalarKeys = [];
  rows.forEach(row => {
    Object.keys(row).forEach(key => {
      if (['user', 'userId', 'idUser', 'idManager', 'idContractor', 'idDealer', 'idUserDealer'].includes(key)) {
        return;
      }
      if (row[key] && typeof row[key] === 'object') {
        return;
      }
      if (!scalarKeys.includes(key)) {
        scalarKeys.push(key);
      }
    });
  });

  let columns = [];
  if (hasUser(rows)) {
    columns.push({ key: 'contractor', label: getAnalyticsUserColumnLabel() });
    columns.push({ key: 'details', label: 'Детали' });
  }
  if (hasPeriod(rows) && !scalarKeys.includes('period')) {
    columns.push({ key: 'period', label: 'Период' });
  }
  scalarKeys.forEach(key => {
    columns.push({ key: key, label: getAnalyticsColumnLabel(key) });
  });

  let tableRows = rows.map((row, index) => {
    let tableRow = {
      __contractorKey: getContractorTableKey(row, index),
      contractor: hasUser(rows) ? getUserLabel(row) : undefined,
      details: hasUser(rows) ? getUserDetails(row, index) : undefined,
      period: getPeriodLabel(row)
    };
    scalarKeys.forEach(key => {
      tableRow[key] = formatRawAnalyticsValue(row[key]);
    });
    return tableRow;
  });

  return { columns: columns, rows: tableRows };
}

function getAnalyticsMetricTitle(metricKey) {
  let titles = {
    timeFirstResponse50: 'Первый ответ менеджера p50',
    timeFirstResponse90: 'Первый ответ менеджера p90',
    timeFirstResponse95: 'Первый ответ менеджера p95',
    maxTimeFirstResponse: 'Первый ответ менеджера максимум',
    timeResponse50: 'Ответ менеджера p50',
    timeResponse90: 'Ответ менеджера p90',
    timeResponse95: 'Ответ менеджера p95',
    timeFirstResponseSla: 'Первый ответ менеджера - SLA',
    timeResponseSla: 'Последующие ответы менеджера - SLA',
    timeBeforeNextTurn50: 'Время до ответа контрагента p50',
    timeBeforeNextTurn90: 'Время до ответа контрагента p90',
    timeBeforeNextTurn95: 'Время до ответа контрагента p95',
    timeManagerFirstResponse50: 'Первый ответ менеджера p50',
    timeManagerFirstResponse90: 'Первый ответ менеджера p90',
    timeManagerFirstResponse95: 'Первый ответ менеджера p95',
    maxTimeManagerFirstResponse: 'Первый ответ менеджера максимум',
    timeManagerResponse50: 'Ответ менеджера p50',
    timeManagerResponse90: 'Ответ менеджера p90',
    timeManagerResponse95: 'Ответ менеджера p95',
    timeManagerFirstResponseSla: 'Первый ответ менеджера - SLA',
    timeManagerResponseSla: 'Последующие ответы менеджера - SLA',
    timeContractorFirstResponse50: 'Ответы контрагента p50',
    timeContractorFirstResponse90: 'Ответы контрагента p90',
    timeContractorFirstResponse95: 'Ответы контрагента p95'
  };
  return titles[metricKey] || getAnalyticsColumnLabel(metricKey);
}

function getAnalyticsColumnLabel(key) {
  let labels = {
    period: 'Период',
    p50: 'p50',
    p90: 'p90',
    p95: 'p95',
    maxTimeResponse: 'Максимум',
    slaPercent: 'SLA',
    withinSla: 'В пределах SLA',
    total: 'Всего ответов',
    openedDialogs: 'Открытые диалоги',
    activeDialogs: 'Активные диалоги',
    dialogDurationP50: 'Длительность p50',
    dialogDurationP90: 'Длительность p90',
    dialogDurationP95: 'Длительность p95',
    messagesCountP50: 'Сообщений p50',
    messagesCountP90: 'Сообщений p90',
    messagesCountP95: 'Сообщений p95'
  };
  return labels[key] || key;
}

function formatRawAnalyticsValue(value) {
  if (value === undefined || value === null || value === '') {
    return '-';
  }
  return value;
}

function getChartMetricFields(metricConfig) {
  let shouldSplit = metricConfig.splitFields
    || (metricConfig.unit === 'hours' && metricConfig.fields.length >= 2);
  if (!shouldSplit || metricConfig.fields.length < 2) {
    return [{
      title: metricConfig.title,
      fields: metricConfig.fields
    }];
  }

  return metricConfig.fields.map(field => {
    return {
      title: metricConfig.title + ' - ' + field.label.toLowerCase(),
      fields: [field]
    };
  });
}

function addDialogTypeConfig(configs, rawRows, title) {
  let rows = normalizeAnalyticsRows(rawRows);
  if (rows.length === 0) {
    return;
  }

  if (shouldRenderMetricAsSummary(rows) || isSingleUserSummaryRows(rows)) {
    let summaryData = buildDialogTypeSummaryData(rows);
    if (summaryData.items.length > 0) {
      configs.push({
        kind: 'summary',
        title: title,
        data: summaryData,
        unit: 'count'
      });
    }
    return;
  }

  let chartData = buildDialogTypeChartData(rows);
  if (chartData.datasets.length === 0) {
    return;
  }

  configs.push({
    title: title,
    type: 'bar',
    data: chartData,
    unit: 'count',
    dialogStats: buildDialogTypeStats(rows),
    indexAxis: 'x',
    stacked: hasUser(rows),
    canToggleLabels: hasUser(rows),
    showDialogPercent: true
  });
}

function normalizeAnalyticsRows(rawRows) {
  if (!rawRows) {
    return [];
  }
  if (Array.isArray(rawRows)) {
    return rawRows.filter(row => row && typeof row === 'object');
  }
  if (typeof rawRows === 'object') {
    let values = Object.values(rawRows);
    let hasObjectRows = values.some(value => value && typeof value === 'object' && !Array.isArray(value));
    let hasScalarValues = values.some(value => !value || typeof value !== 'object');
    if (hasObjectRows && !hasScalarValues) {
      return values.filter(row => row && typeof row === 'object');
    }
    return [rawRows];
  }
  return [];
}

function getComparablePeriods(rows) {
  return uniqueValues(rows.map(row => row.period).filter(period => period && period !== 'all'));
}

function hasComparablePeriod(rows) {
  return getComparablePeriods(rows).length > 0;
}

function shouldRenderMetricAsChart(rows) {
  if (rows.length === 0) {
    return false;
  }
  if (getComparablePeriods(rows).length > 1) {
    return true;
  }
  if (hasUser(rows) && uniqueValues(rows.map(getUserLabel)).length > 1) {
    return true;
  }
  if (uniqueValues(rows.map(row => row.typeDialog).filter(Boolean)).length > 1) {
    return true;
  }
  if (rows.length > 1 && hasComparablePeriod(rows)) {
    return true;
  }
  return false;
}

function shouldRenderMetricAsSummary(rows) {
  return rows.length > 0 && !shouldRenderMetricAsChart(rows);
}

function isSummaryMetricRows(rows) {
  return shouldRenderMetricAsSummary(rows);
}

function buildSummaryMetricData(row, fields, unit) {
  let items = fields.map(field => {
    return {
      label: field.label,
      value: getSummaryItemValue(row[field.key], unit)
    };
  }).filter(item => item.value !== null);

  return { items: items };
}

function isSingleUserSummaryRows(rows) {
  return rows.length > 0 && !hasComparablePeriod(rows) && uniqueValues(rows.map(row => getUserLabel(row))).length === 1;
}

function isSingleUserPeriodRows(rows) {
  return rows.length > 0 && hasComparablePeriod(rows) && uniqueValues(rows.map(row => getUserLabel(row))).length === 1;
}

function buildDialogTypeSummaryData(rows) {
  let totalDialogs = rows.reduce((sum, row) => sum + (Number(row.total) || 0), 0);
  let items = rows.map(row => {
    let typeInfo = getDialogTypeInfo(row.typeDialog);
    let value = prepareChartValue(row.total, 'count');
    let percent = totalDialogs > 0 ? Math.round(((Number(row.total) || 0) / totalDialogs) * 1000) / 10 : 0;
    return {
      label: typeInfo.label + ' (' + percent + '%)',
      value: value
    };
  }).filter(item => item.value !== null);

  return { items: items };
}

function buildMetricTableData(rows, fields, unit) {
  if (hasPeriod(rows)) {
    return buildPeriodMetricTableData(rows, fields, unit);
  }

  let columns = [];
  columns.push({ key: 'contractor', label: getAnalyticsUserColumnLabel() });
  columns.push({ key: 'details', label: 'Детали' });
  fields.forEach(field => {
    columns.push({ key: field.key, label: field.label, unit: unit });
  });

  let tableRows = rows.map((row, index) => {
    let tableRow = {
      __contractorKey: getContractorTableKey(row, index),
      period: getPeriodLabel(row),
      contractor: getUserLabel(row),
      details: getUserDetails(row, index)
    };
    fields.forEach(field => {
      tableRow[field.key] = formatMetricCellValue(row[field.key], unit);
    });
    return tableRow;
  });

  return { columns: columns, rows: tableRows };
}

function buildPeriodMetricTableData(rows, fields, unit) {
  let periods = uniqueValues(rows.map(row => getPeriodLabel(row)));
  let groupedRows = {};

  rows.forEach((row, index) => {
    let rowKey = getContractorTableKey(row, index);
    if (!groupedRows[rowKey]) {
      groupedRows[rowKey] = {
        __contractorKey: rowKey,
        contractor: getUserLabel(row),
        details: getUserDetails(row, index)
      };
    }

    fields.forEach(field => {
      let columnKey = getPeriodColumnKey(getPeriodLabel(row), field.key);
      groupedRows[rowKey][columnKey] = formatMetricCellValue(row[field.key], unit);
    });
  });

  let columns = [
    { key: 'contractor', label: getAnalyticsUserColumnLabel() },
    { key: 'details', label: 'Детали' }
  ];
  periods.forEach(period => {
    fields.forEach(field => {
      columns.push({
        key: getPeriodColumnKey(period, field.key),
        label: period + ' - ' + field.label,
        unit: unit
      });
    });
  });

  return { columns: columns, rows: Object.values(groupedRows) };
}

function buildMetricChartData(rows, fields, unit) {
  let rowHasPeriod = hasComparablePeriod(rows);
  let rowHasUser = hasUser(rows);
  if (rowHasPeriod && rowHasUser) {
    return buildPeriodUserChartData(rows, fields, unit);
  }
  return buildSimpleMetricChartData(rows, fields, unit, rowHasPeriod);
}

function buildSimpleMetricChartData(rows, fields, unit, rowHasPeriod) {
  let labels = rows.map((row, index) => rowHasPeriod ? getPeriodLabel(row) : getRowLabel(row, index));
  let datasets = fields.map((field, index) => {
    let colorIndex = field.colorIndex ?? index;
    let labelColors = rows.map(row => getStableEntityColor(getUserColorKey(row), 0.65));
    let labelBorderColors = rows.map(row => getStableEntityColor(getUserColorKey(row), 1));
    return {
      label: field.label,
      data: rows.map(row => prepareChartValue(row[field.key], unit)),
      durationSeconds: isDurationUnit(unit)
        ? rows.map(row => {
          let seconds = Number(row[field.key]);
          return Number.isFinite(seconds) ? seconds : null;
        })
        : undefined,
      backgroundColor: hasUser(rows) ? labelColors : getAnalyticsColor(colorIndex, 0.65),
      borderColor: hasUser(rows) ? labelBorderColors : getAnalyticsColor(colorIndex, 1),
      borderWidth: 2,
      tension: 0.25
    };
  }).filter(dataset => dataset.data.some(value => value !== null));

  return {
    labels: labels,
    labelEntityKeys: hasUser(rows) ? rows.map(row => getUserColorKey(row)) : null,
    datasets: datasets
  };
}

function buildPeriodUserChartData(rows, fields, unit) {
  let labels = uniqueValues(rows.map(row => getPeriodLabel(row)));
  let users = getUniqueUserItems(rows);
  let datasets = [];

  users.forEach(user => {
    fields.forEach((field, fieldIndex) => {
      datasets.push({
        label: fields.length > 1 ? user.label + ' - ' + field.label : user.label,
        entityKey: user.key,
        data: labels.map(periodLabel => {
          let row = rows.find(item => getPeriodLabel(item) === periodLabel && getUserColorKey(item) === user.key);
          return row ? prepareChartValue(row[field.key], unit) : null;
        }),
        durationSeconds: isDurationUnit(unit)
          ? labels.map(periodLabel => {
            let row = rows.find(item => getPeriodLabel(item) === periodLabel && getUserColorKey(item) === user.key);
            if (!row) {
              return null;
            }
            let seconds = Number(row[field.key]);
            return Number.isFinite(seconds) ? seconds : null;
          })
          : undefined,
        backgroundColor: getStableEntityColor(user.key, 0.35),
        borderColor: getStableEntityColor(user.key, 1),
        pointBackgroundColor: getStableEntityColor(user.key, 1),
        pointBorderColor: getStableEntityColor(user.key, 1),
        borderWidth: 2,
        tension: 0.25
      });
    });
  });

  datasets = datasets.filter(dataset => dataset.data.some(value => value !== null));
  return { labels: labels, datasets: datasets };
}

function buildDialogTypeChartData(rows) {
  if (hasUser(rows)) {
    return buildDialogTypeByUserChartData(rows);
  }

  let totalsByType = {};
  rows.forEach(row => {
    let typeInfo = getDialogTypeInfo(row.typeDialog);
    if (!totalsByType[typeInfo.key]) {
      totalsByType[typeInfo.key] = {
        label: typeInfo.label,
        colorIndex: typeInfo.colorIndex,
        total: 0
      };
    }
    totalsByType[typeInfo.key].total += Number(row.total) || 0;
  });
  let typeRows = Object.values(totalsByType);

  return {
    labels: typeRows.map(row => row.label),
    datasets: [{
      label: 'Диалоги',
      data: typeRows.map(row => prepareChartValue(row.total, 'count')),
      backgroundColor: typeRows.map(row => getAnalyticsColor(row.colorIndex, 0.65)),
      borderColor: typeRows.map(row => getAnalyticsColor(row.colorIndex, 1)),
      borderWidth: 1
    }]
  };
}

function buildDialogTypeByUserChartData(rows) {
  let users = getUniqueUserItems(rows);
  let labels = users.map(user => user.label);
  let types = uniqueValues(rows.map(row => getDialogTypeInfo(row.typeDialog).key));
  let datasets = types.map((typeKey, index) => {
    let typeInfo = getDialogTypeInfo(typeKey);
    return {
      label: typeInfo.label,
      data: users.map(user => {
        return rows.reduce((sum, row) => {
          if (getUserColorKey(row) !== user.key || getDialogTypeInfo(row.typeDialog).key !== typeKey) {
            return sum;
          }
          return sum + (Number(row.total) || 0);
        }, 0);
      }),
      backgroundColor: getAnalyticsColor(typeInfo.colorIndex ?? index, 0.65),
      borderColor: getAnalyticsColor(typeInfo.colorIndex ?? index, 1),
      borderWidth: 1
    };
  });

  return {
    labels: labels,
    labelEntityKeys: users.map(user => user.key),
    datasets: datasets
  };
}

function getPeriodColumnKey(period, key) {
  return 'period_' + String(period).replace(/[^a-zA-Z0-9а-яА-Я]+/g, '_') + '_' + key;
}

function getContractorTableKey(row, index) {
  if (row.userId) {
    return 'user_' + row.userId;
  }
  return getUserLabel(row) || 'row_' + index;
}

function buildDialogTypeStats(rows) {
  let totalsByType = {};
  rows.forEach(row => {
    let typeInfo = getDialogTypeInfo(row.typeDialog);
    if (!totalsByType[typeInfo.key]) {
      totalsByType[typeInfo.key] = {
        label: typeInfo.label,
        description: typeInfo.description,
        colorIndex: typeInfo.colorIndex,
        total: 0
      };
    }
    totalsByType[typeInfo.key].total += Number(row.total) || 0;
  });

  let stats = Object.values(totalsByType);
  let totalDialogs = stats.reduce((sum, item) => sum + item.total, 0);
  stats.forEach(item => {
    item.percent = totalDialogs > 0 ? Math.round((item.total / totalDialogs) * 1000) / 10 : 0;
  });
  return stats.sort((a, b) => b.total - a.total);
}

function renderAnalyticsChart(parent, config) {
  if (config.kind === 'table') {
    renderAnalyticsTable(parent, config);
    return;
  }

  if (config.kind === 'summary') {
    renderAnalyticsSummary(parent, config);
    return;
  }

  let card = createTagHtml('div', 'analytics_chart_card');

  let title = createTagHtml('h3', 'analytics_chart_title');
  title.textContent = config.title;

  let canvasWrap = createTagHtml('div', 'analytics_chart_canvas');

  let canvas = createTagHtml('canvas');
  massAppendChild(canvasWrap, canvas);
  massAppendChild(card, title, canvasWrap);
  if (config.dialogStats && config.dialogStats.length > 0) {
    card.appendChild(renderDialogTypeStats(config.dialogStats));
  }
  parent.appendChild(card);

  let sourceData = cloneChartData(config.data);
  let chart = new Chart(canvas, {
    type: config.type,
    data: getFilteredChartData(sourceData),
    options: getAnalyticsChartOptions(config)
  });
  analyticsChartStates.push({
    chart: chart,
    sourceData: sourceData
  });
  if (config.canToggleLabels && getChartToggleItems(sourceData).length > 1) {
    card.appendChild(renderChartLabelToggles(sourceData));
  }
  analyticsCharts.push(chart);
}

function renderAnalyticsTable(parent, config) {
  let card = createTagHtml('div', 'analytics_chart_card');
  card.classList.add('analytics_table_card');

  let title = createTagHtml('h3', 'analytics_chart_title');
  title.textContent = config.title;
  card.appendChild(title);
  renderAnalyticsTableContent(card, config);
  parent.appendChild(card);
}

function getContractorDealerParameter(config, parameterId) {
  if (!config.parameters || config.parameters.length === 0) {
    return null;
  }
  return config.parameters.find(parameter => parameter.id === parameterId) || config.parameters[0];
}

function buildParameterTableData(config, parameter) {
  let columns = [
    { key: 'contractor', label: getAnalyticsUserColumnLabel() },
    { key: 'details', label: 'Детали' }
  ];
  parameter.columns.forEach(column => {
    columns.push({
      key: column.key,
      label: column.label,
      unit: parameter.unit
    });
  });
  return {
    columns: columns,
    rows: config.data.rows
  };
}

function parseUnifiedTableCellValue(value, unit) {
  if (value === undefined || value === null || value === '-') {
    return null;
  }
  if (typeof value === 'number' && Number.isFinite(value)) {
    return isDurationUnit(unit) ? getChartDurationValue(value) : value;
  }
  if (isDurationUnit(unit)) {
    let seconds = parseDurationSeconds(value);
    return seconds === null ? null : getChartDurationValue(seconds);
  }
  if (unit === 'percent') {
    let numberValue = Number(String(value).replace('%', '').replace(',', '.').trim());
    return Number.isFinite(numberValue) ? numberValue : null;
  }
  let numberValue = Number(String(value).replace(',', '.').trim());
  if (Number.isFinite(numberValue)) {
    return numberValue;
  }
  return unit === 'raw' ? null : null;
}

function getContractorDealerRankChartParameters(parameters) {
  if (!Array.isArray(parameters) || parameters.length === 0) {
    return [];
  }
  let slaParameters = [];
  let otherParameters = [];
  parameters.forEach(parameter => {
    if (parameter.unit === 'percent' || /Sla_slaPercent$/i.test(String(parameter.id || ''))) {
      slaParameters.push(parameter);
      return;
    }
    otherParameters.push(parameter);
  });
  return slaParameters.concat(otherParameters);
}

function buildContractorChartColorIndexMap(rows) {
  let colorIndexByEntity = new Map();
  uniqueValues(rows.map(row => row.__contractorKey).filter(Boolean))
    .sort()
    .forEach((entityKey, index) => {
      colorIndexByEntity.set(entityKey, index);
    });
  return colorIndexByEntity;
}

function buildContractorDealerParameterChartData(rows, parameter) {
  let colorIndexByEntity = buildContractorChartColorIndexMap(rows);

  if (parameter.columns.length === 1) {
    let column = parameter.columns[0];
    return {
      labels: rows.map(row => row.contractor),
      labelEntityKeys: rows.map(row => row.__contractorKey),
      datasets: [{
        label: parameter.label,
        data: rows.map(row => parseUnifiedTableCellValue(row[column.key], parameter.unit)),
        backgroundColor: rows.map(row => getAnalyticsColor(colorIndexByEntity.get(row.__contractorKey), 0.75)),
        borderColor: rows.map(row => getAnalyticsColor(colorIndexByEntity.get(row.__contractorKey), 1)),
        borderWidth: 2,
        tension: 0.25
      }]
    };
  }

  let labels = parameter.columns.map(column => column.label);
  let datasets = rows.map(row => {
    let colorIndex = colorIndexByEntity.get(row.__contractorKey);
    let color = getAnalyticsColor(colorIndex, 1);
    return {
      label: row.contractor,
      entityKey: row.__contractorKey,
      data: parameter.columns.map(column => parseUnifiedTableCellValue(row[column.key], parameter.unit)),
      backgroundColor: getAnalyticsColor(colorIndex, 0.25),
      borderColor: color,
      pointBackgroundColor: color,
      pointBorderColor: color,
      pointRadius: 4,
      pointHoverRadius: 6,
      borderWidth: 2,
      tension: 0.25
    };
  }).filter(dataset => dataset.data.some(value => value !== null));

  return {
    labels: labels,
    datasets: datasets
  };
}

function renderContractorDealerMetricCharts(parent, config, contractorKeys, titleSuffix) {
  let filteredRows = config.data.rows.filter(row => contractorKeys.has(row.__contractorKey));
  if (filteredRows.length === 0) {
    let message = createTagHtml('div', 'analytics_message');
    message.textContent = 'Нет данных для отображения';
    parent.appendChild(message);
    return;
  }

  let chartsGrid = createTagHtml('div', 'analytics_contractor_rank_charts');
  getContractorDealerRankChartParameters(config.parameters).forEach(parameter => {
    let chartData = buildContractorDealerParameterChartData(filteredRows, parameter);
    if (!chartData.datasets.some(dataset => dataset.data.some(value => value !== null))) {
      return;
    }
    renderAnalyticsChart(chartsGrid, {
      title: parameter.label + titleSuffix,
      type: parameter.columns.length > 1 ? 'line' : 'bar',
      data: chartData,
      unit: parameter.unit,
      canToggleLabels: filteredRows.length > 1
    });
  });
  parent.appendChild(chartsGrid);
}

function getDealerRowUserId(row) {
  if (row.idUserDealer) {
    return row.idUserDealer;
  }
  if (row.userId) {
    return row.userId;
  }
  if (row.user && row.user.id) {
    return row.user.id;
  }
  let key = row.__contractorKey || '';
  if (key.indexOf('user_') === 0) {
    let id = Number(key.slice(5));
    return Number.isFinite(id) && id > 0 ? id : null;
  }
  return null;
}

function shouldShowDealerDependentsLink() {
  return analyticsWhoseMode === 'dealer';
}

function getDealerDependentsCount(dealerUserId, dealerDependentsByDealer) {
  if (!dealerUserId || !dealerDependentsByDealer) {
    return 0;
  }
  let count = dealerDependentsByDealer[dealerUserId]
    ?? dealerDependentsByDealer[String(dealerUserId)]
    ?? dealerDependentsByDealer[Number(dealerUserId)]
    ?? 0;
  return Number(count) || 0;
}

function dealerHasDependents(dealerUserId, dealerDependentsByDealer) {
  return getDealerDependentsCount(dealerUserId, dealerDependentsByDealer) > 0;
}

function getContractorDealerTableRenderOptions(config, extraOptions = {}) {
  let options = Object.assign({}, extraOptions);
  if (shouldShowDealerDependentsLink() && config) {
    options.dealerDependentsByDealer = (config.rawData && config.rawData.dealerDependentsByDealer)
      || config.dealerDependentsByDealer
      || {};
  }
  return options;
}

function createAnalyticsDetailsTextElement(row, cellValue, options) {
  let detailsText = createTagHtml('div', 'analytics_details_text');
  detailsText.textContent = cellValue;

  if (!options.dealerDependentsByDealer || !shouldShowDealerDependentsLink()) {
    return detailsText;
  }

  let dealerUserId = getDealerRowUserId(row);
  if (!dealerHasDependents(dealerUserId, options.dealerDependentsByDealer)) {
    return detailsText;
  }

  detailsText.classList.add('analytics_details_text_clickable');
  detailsText.addEventListener('click', () => {
    openDealerDependentsAnalytics(dealerUserId, row.contractor);
  });
  return detailsText;
}

function openDealerDependentsAnalytics(dealerUserId, dealerName) {
  if (!dealerUserId) {
    return;
  }
  analyticsDealerDrilldownContext = {
    dealerUserId: dealerUserId,
    dealerName: dealerName || ''
  };
  simpleSendData(JSON.stringify({ dealerUserId: dealerUserId }), 'getDealerDependentsAnalytics');
}

function closeDealerDependentsAnalytics() {
  analyticsDealerDrilldownContext = null;
  analyticsWhoseMode = analyticsWhoseModeBeforeDrilldown || 'dealer';
  analyticsWhoseModeBeforeDrilldown = null;
  if (analyticsDealerDrilldownParentSnapshot) {
    analyticsOnPage(JSON.parse(JSON.stringify(analyticsDealerDrilldownParentSnapshot)), { restoreDealer: true });
    return;
  }
  sendShowWhere(where);
}

function renderDealerDependentsDashboard(parent, data) {
  let context = data.dealerDependentsContext || analyticsDealerDrilldownContext || {};
  let dealerName = context.dealerName || 'Дилер';

  let toolbar = createTagHtml('div', 'analytics_dealer_dependents_toolbar');
  let backButton = createTagHtml('button', 'analytics_dealer_dependents_back', '', '', 'button');
  backButton.textContent = '← Назад к дилерам';
  backButton.addEventListener('click', closeDealerDependentsAnalytics);
  let title = createTagHtml('h3', 'analytics_chart_title');
  title.textContent = dealerName + ' — контрагенты на точках';
  massAppendChild(toolbar, backButton, title);
  parent.appendChild(toolbar);

  let chartConfigs = getAnalyticsChartConfigs(data);
  let unifiedConfig = chartConfigs.find(config => config.isUnifiedContractorTable);
  if (!unifiedConfig || !unifiedConfig.data.rows.length) {
    renderAnalyticsMessage(parent, 'Нет данных по контрагентам дилера');
    return;
  }

  let chartsHost = createTagHtml('div', 'analytics_dealer_dependents_charts');
  let allKeys = new Set(unifiedConfig.data.rows.map(row => row.__contractorKey));
  renderContractorDealerMetricCharts(chartsHost, unifiedConfig, allKeys, '');
  parent.appendChild(chartsHost);
}

function renderContractorDealerDashboard(parent, config) {
  let card = createTagHtml('div', 'analytics_chart_card');
  card.classList.add('analytics_table_card');
  let selectedContractors = analyticsSelectedContractorKeys;
  let currentMode = analyticsContractorMode === 'selected' && selectedContractors.size === 0 ? 'all' : analyticsContractorMode;
  let currentParameterId = config.parameters[0] ? config.parameters[0].id : null;

  let title = createTagHtml('h3', 'analytics_chart_title');
  title.textContent = config.title;
  card.appendChild(title);

  let modeSwitcher = createTagHtml('div', 'analytics_contractor_mode_switcher');
  let contentHost = createTagHtml('div', 'analytics_contractor_mode_host');
  let modeButtons = new Map();
  let modes = [
    { key: 'all', label: 'Таблица' },
    { key: 'active', label: 'TOP-10' },
    { key: 'worse', label: 'WORSE-10' }
  ];
  if (selectedContractors.size > 0) {
    modes.push({ key: 'selected', label: 'Выбранные' });
  }

  function setActiveModeButton(modeKey) {
    modeButtons.forEach((button, key) => {
      button.classList.toggle('analytics_contractor_mode_button_active', key === modeKey);
    });
  }

  function renderContent() {
    destroyChartsInElement(contentHost);
    contentHost.innerHTML = '';

    if (currentMode === 'active') {
      renderContractorDealerMetricCharts(
        contentHost,
        config,
        getMostActiveContractorKeys([config]),
        ' - TOP-10'
      );
      return;
    }

    if (currentMode === 'worse') {
      renderContractorDealerWorseView(contentHost, config);
      return;
    }

    if (currentMode === 'selected') {
      if (shouldRenderContractorDealerSelectedAsSummaries(config, selectedContractors)) {
        renderContractorDealerSelectedSummaries(contentHost, config, selectedContractors);
      } else {
        renderContractorDealerMetricCharts(
          contentHost,
          config,
          selectedContractors,
          ' - выбранные'
        );
      }
      return;
    }

    renderContractorDealerTableView(contentHost, config, currentParameterId, selectedContractors, {
      onParameterChange: parameterId => {
        currentParameterId = parameterId;
        renderContent();
      },
      onSelectionChange: () => {
        let hasSelectedMode = modes.some(mode => mode.key === 'selected');
        if (selectedContractors.size > 0 && !hasSelectedMode) {
          modes.push({ key: 'selected', label: 'Выбранные' });
          let button = createTagHtml('button', 'analytics_contractor_mode_button', '', '', 'button');
          button.textContent = 'Выбранные';
          button.addEventListener('click', () => {
            currentMode = 'selected';
            analyticsContractorMode = 'selected';
            setActiveModeButton('selected');
            renderContent();
          });
          modeSwitcher.appendChild(button);
          modeButtons.set('selected', button);
          return;
        }

        if (selectedContractors.size === 0 && hasSelectedMode) {
          let selectedModeIndex = modes.findIndex(mode => mode.key === 'selected');
          if (selectedModeIndex !== -1) {
            modes.splice(selectedModeIndex, 1);
          }
          let selectedModeButton = modeButtons.get('selected');
          if (selectedModeButton) {
            selectedModeButton.remove();
            modeButtons.delete('selected');
          }
          if (currentMode === 'selected') {
            currentMode = 'all';
            analyticsContractorMode = 'all';
            setActiveModeButton('all');
            renderContent();
          }
        }
      },
      onShowSelectedCharts: () => {
        if (selectedContractors.size === 0) {
          return;
        }
        currentMode = 'selected';
        analyticsContractorMode = 'selected';
        setActiveModeButton('selected');
        renderContent();
      }
    });
  }

  modes.forEach(mode => {
    let button = createTagHtml('button', 'analytics_contractor_mode_button', '', '', 'button');
    if (mode.key === currentMode) {
      button.classList.add('analytics_contractor_mode_button_active');
    }
    button.textContent = mode.label;
    button.addEventListener('click', () => {
      currentMode = mode.key;
      analyticsContractorMode = mode.key;
      setActiveModeButton(mode.key);
      renderContent();
    });
    modeButtons.set(mode.key, button);
    modeSwitcher.appendChild(button);
  });

  card.appendChild(modeSwitcher);
  renderContent();
  card.appendChild(contentHost);
  parent.appendChild(card);
}

function renderContractorDealerTableView(parent, config, parameterId, selectedContractors, callbacks) {
  let parameter = getContractorDealerParameter(config, parameterId);
  if (!parameter) {
    return;
  }

  let parameterSwitcher = createTagHtml('div', 'analytics_table_switcher');
  config.parameters.forEach(item => {
    let button = createTagHtml('button', 'analytics_table_switcher_button', '', '', 'button');
    if (item.id === parameter.id) {
      button.classList.add('analytics_table_switcher_button_active');
    }
    button.textContent = item.label;
    button.addEventListener('click', () => {
      parameterSwitcher.querySelectorAll('.analytics_table_switcher_button').forEach(node => {
        node.classList.remove('analytics_table_switcher_button_active');
      });
      button.classList.add('analytics_table_switcher_button_active');
      if (callbacks.onParameterChange) {
        callbacks.onParameterChange(item.id);
      }
    });
    parameterSwitcher.appendChild(button);
  });

  let selectionControls = createTagHtml('div', 'analytics_contractor_selection');
  let selectedCount = createTagHtml('span', 'analytics_contractor_selection_count');
  let selectedButton = createTagHtml('button', 'analytics_contractor_selection_button', '', '', 'button');
  selectedButton.textContent = 'Графики выбранных';
  let updateSelectedCount = () => {
    selectedCount.textContent = 'Выбрано: ' + selectedContractors.size;
    selectedButton.disabled = selectedContractors.size === 0;
    if (callbacks.onSelectionChange) {
      callbacks.onSelectionChange();
    }
  };
  selectedButton.addEventListener('click', () => {
    if (callbacks.onShowSelectedCharts) {
      callbacks.onShowSelectedCharts();
    }
  });
  updateSelectedCount();
  massAppendChild(selectionControls, selectedCount, selectedButton);

  let tableHost = createTagHtml('div', 'analytics_table_host');
  renderAnalyticsTableContent(tableHost, {
    title: parameter.label,
    data: buildParameterTableData(config, parameter)
  }, getContractorDealerTableRenderOptions(config, {
    selectable: true,
    selectedContractors: selectedContractors,
    onSelectionChange: updateSelectedCount
  }));

  massAppendChild(parent, parameterSwitcher, selectionControls, tableHost);
}

function renderAnalyticsTableSwitcher(parent, configs) {
  let unifiedConfig = configs.find(config => config.isUnifiedContractorTable);
  if (unifiedConfig) {
    renderContractorDealerDashboard(parent, unifiedConfig);
    return;
  }

  let card = createTagHtml('div', 'analytics_chart_card');
  card.classList.add('analytics_table_card');
  let selectedContractors = analyticsSelectedContractorKeys;
  let currentMode = analyticsContractorMode === 'selected' && selectedContractors.size === 0 ? 'all' : analyticsContractorMode;

  let title = createTagHtml('h3', 'analytics_chart_title');
  title.textContent = getAnalyticsUsersTableTitle();
  card.appendChild(title);

  let modeSwitcher = createTagHtml('div', 'analytics_contractor_mode_switcher');
  let contentHost = createTagHtml('div', 'analytics_contractor_mode_host');
  let modeButtons = new Map();
  let modes = [
    { key: 'all', label: 'Весь список' },
    { key: 'active', label: 'TOP-10' },
    { key: 'worse', label: 'WORSE-10' }
  ];
  if (selectedContractors.size > 0) {
    modes.push({ key: 'selected', label: 'Выбранные' });
  }

  function setActiveModeButton(modeKey) {
    modeButtons.forEach((button, key) => {
      button.classList.toggle('analytics_contractor_mode_button_active', key === modeKey);
    });
  }

  function syncSelectedModeButton() {
    let hasSelectedMode = modes.some(mode => mode.key === 'selected');
    if (selectedContractors.size > 0 && !hasSelectedMode) {
      modes.push({ key: 'selected', label: 'Выбранные' });
      let button = createTagHtml('button', 'analytics_contractor_mode_button', '', '', 'button');
      button.textContent = 'Выбранные';
      button.addEventListener('click', () => {
        currentMode = 'selected';
        analyticsContractorMode = 'selected';
        setActiveModeButton('selected');
        renderModeContent();
      });
      modeSwitcher.appendChild(button);
      modeButtons.set('selected', button);
      return;
    }

    if (selectedContractors.size === 0 && hasSelectedMode) {
      let selectedModeIndex = modes.findIndex(mode => mode.key === 'selected');
      if (selectedModeIndex !== -1) {
        modes.splice(selectedModeIndex, 1);
      }
      let selectedModeButton = modeButtons.get('selected');
      if (selectedModeButton) {
        selectedModeButton.remove();
        modeButtons.delete('selected');
      }
      if (currentMode === 'selected') {
        currentMode = 'all';
        analyticsContractorMode = 'all';
        setActiveModeButton('all');
        renderModeContent();
      }
    }
  }

  function renderModeContent() {
    destroyChartsInElement(contentHost);
    contentHost.innerHTML = '';
    renderContractorModeContent(contentHost, configs, currentMode, selectedContractors, {
      onSelectionChange: syncSelectedModeButton
    });
  }

  modes.forEach(mode => {
    let button = createTagHtml('button', 'analytics_contractor_mode_button', '', '', 'button');
    if (mode.key === currentMode) {
      button.classList.add('analytics_contractor_mode_button_active');
    }
    button.textContent = mode.label;
    button.addEventListener('click', () => {
      currentMode = mode.key;
      analyticsContractorMode = mode.key;
      setActiveModeButton(mode.key);
      renderModeContent();
    });
    modeButtons.set(mode.key, button);
    modeSwitcher.appendChild(button);
  });

  card.appendChild(modeSwitcher);
  renderModeContent();
  card.appendChild(contentHost);
  parent.appendChild(card);
}

function renderContractorModeContent(parent, configs, mode, selectedContractors, contentOptions = {}) {
  if (mode === 'active') {
    renderContractorActiveCharts(parent, configs);
    return;
  }
  if (mode === 'worse') {
    let unifiedConfig = configs.find(config => config.isUnifiedContractorTable);
    if (unifiedConfig) {
      renderContractorDealerWorseView(parent, unifiedConfig);
    } else {
      renderAnalyticsMessage(parent, 'WORSE-10 доступен только для сводной таблицы контрагентов');
    }
    return;
  }
  if (mode === 'selected') {
    renderContractorSelectedCharts(parent, configs, selectedContractors);
    return;
  }

  let selectionControls = createTagHtml('div', 'analytics_contractor_selection');
  let selectedCount = createTagHtml('span', 'analytics_contractor_selection_count');
  let selectedButton = createTagHtml('button', 'analytics_contractor_selection_button', '', '', 'button');
  selectedButton.textContent = 'Показать выбранных';
  let updateSelectedCount = () => {
    selectedCount.textContent = 'Выбрано: ' + selectedContractors.size;
    selectedButton.disabled = selectedContractors.size === 0;
    if (contentOptions.onSelectionChange) {
      contentOptions.onSelectionChange();
    }
  };
  selectedButton.addEventListener('click', () => {
    analyticsContractorMode = 'selected';
    destroyChartsInElement(tableHost);
    tableHost.innerHTML = '';
    renderContractorSelectedCharts(tableHost, configs, selectedContractors);
  });
  massAppendChild(selectionControls, selectedCount, selectedButton);
  updateSelectedCount();

  let switcher = createTagHtml('div', 'analytics_table_switcher');
  let tableHost = createTagHtml('div', 'analytics_table_host');
  let tableOptions = {
    selectable: true,
    selectedContractors: selectedContractors,
    onSelectionChange: updateSelectedCount
  };

  configs.forEach((config, index) => {
    let button = createTagHtml('button', 'analytics_table_switcher_button', '', '', 'button');
    if (index === 0) {
      button.classList.add('analytics_table_switcher_button_active');
    }
    button.textContent = config.title;
    button.addEventListener('click', () => {
      switcher.querySelectorAll('.analytics_table_switcher_button').forEach(item => {
        item.classList.remove('analytics_table_switcher_button_active');
      });
      button.classList.add('analytics_table_switcher_button_active');
      destroyChartsInElement(tableHost);
      tableHost.innerHTML = '';
      renderAnalyticsTableContent(tableHost, config, tableOptions);
    });
    switcher.appendChild(button);
  });

  renderAnalyticsTableContent(tableHost, configs[0], tableOptions);
  massAppendChild(parent, selectionControls, switcher, tableHost);
}

function destroyChartsInElement(element) {
  analyticsCharts = analyticsCharts.filter(chart => {
    if (chart.canvas && element.contains(chart.canvas)) {
      chart.destroy();
      return false;
    }
    return true;
  });
  analyticsChartStates = analyticsChartStates.filter(state => state.chart.canvas && !element.contains(state.chart.canvas));
}

function renderContractorActiveCharts(parent, configs) {
  let unifiedConfig = configs.find(config => config.isUnifiedContractorTable);
  if (unifiedConfig) {
    renderContractorDealerMetricCharts(
      parent,
      unifiedConfig,
      getMostActiveContractorKeys(configs),
      ' - TOP-10'
    );
    return;
  }

  let chartsGrid = createTagHtml('div', 'analytics_contractor_rank_charts');
  let titleSuffix = ' - TOP-10';
  let activeContractors = getMostActiveContractorKeys(configs);

  configs.forEach(config => {
    if (!config.sourceRows || config.sourceRows.length === 0) {
      return;
    }

    let activeRows = getContractorRowsByKeys(config.sourceRows, activeContractors);
    if (activeRows.length === 0) {
      return;
    }

    getChartMetricFields({
      title: config.title,
      unit: config.unit,
      fields: config.fields
    }).forEach(fieldGroup => {
      let chartData = buildMetricChartData(activeRows, fieldGroup.fields, config.unit);
      if (chartData.datasets.length > 0) {
        renderAnalyticsChart(chartsGrid, {
          title: fieldGroup.title + titleSuffix,
          type: getMetricChartType(activeRows),
          data: chartData,
          unit: config.unit,
          canToggleLabels: hasUser(activeRows)
        });
      }
    });
  });

  parent.appendChild(chartsGrid);
}

function getUnifiedTablePeriods(config) {
  if (!config || !config.parameters || config.parameters.length === 0) {
    return [];
  }
  let columns = config.parameters[0].columns || [];
  if (columns.length <= 1) {
    return [];
  }
  return columns.map(column => column.label).filter(Boolean);
}

const analyticsWorseScoreComponents = [
  { id: 'ordersPerDialog', columnId: 'dataDialogs_ordersPerDialog', unit: 'ratio', kind: 'low', weight: 0.25, label: 'Заказы / Диалоги' },
  { id: 'ordersPerTurn', columnId: 'dataDialogs_ordersPerTurn', unit: 'ratio', kind: 'low', weight: 0.25, label: 'Заказы / Сообщения' },
  { id: 'messagesCount', columnId: 'dataDialogs_messagesCountP90', unit: 'count', kind: 'high', weight: 0.25, label: 'Сообщений в диалоге p90' },
  { id: 'complaintsPerOrder', columnId: 'dataDialogs_complaintsPerOrder', unit: 'ratio', kind: 'high', weight: 0.25, label: 'Рекламации / Заказы' }
];

function parseUnifiedTableRawNumeric(value, unit) {
  if (value === undefined || value === null || value === '-') {
    return null;
  }
  if (typeof value === 'number' && Number.isFinite(value)) {
    return value;
  }
  if (isDurationUnit(unit)) {
    return parseDurationSeconds(value);
  }
  if (unit === 'percent') {
    let numberValue = Number(String(value).replace('%', '').replace(',', '.').trim());
    return Number.isFinite(numberValue) ? numberValue : null;
  }
  let numberValue = Number(String(value).replace(',', '.').trim());
  return Number.isFinite(numberValue) ? numberValue : null;
}

function averageUnifiedRowMetric(row, columnId, unit, periods) {
  let values = [];
  if (!periods || periods.length === 0) {
    let value = parseUnifiedTableRawNumeric(row[columnId], unit);
    return value === null ? null : value;
  }
  periods.forEach(period => {
    let value = parseUnifiedTableRawNumeric(row[getPeriodColumnKey(period, columnId)], unit);
    if (value !== null) {
      values.push(value);
    }
  });
  if (values.length === 0) {
    return null;
  }
  return values.reduce((sum, value) => sum + value, 0) / values.length;
}

function getAnalyticsMedian(values) {
  if (!values.length) {
    return 0;
  }
  let sorted = values.slice().sort((a, b) => a - b);
  let middle = Math.floor(sorted.length / 2);
  if (sorted.length % 2 === 0) {
    return (sorted[middle - 1] + sorted[middle]) / 2;
  }
  return sorted[middle];
}

function normalizeWorseHighValue(value, reference) {
  if (value === null || !Number.isFinite(value)) {
    return null;
  }
  let base = reference > 0 ? reference : 1;
  return Math.min(value / base, 1);
}

function normalizeWorseLowValue(value, reference) {
  if (value === null || !Number.isFinite(value)) {
    return null;
  }
  let base = reference > 0 ? reference : 1;
  return 1 - Math.min(value / base, 1);
}

function getUnifiedRowOpenedDialogs(row, periods) {
  if (!periods || periods.length === 0) {
    let value = parseUnifiedTableRawNumeric(row.dataDialogs_openedDialogs, 'count');
    return value === null ? null : value;
  }

  let sum = 0;
  let hasValue = false;
  periods.forEach(period => {
    let value = parseUnifiedTableRawNumeric(row[getPeriodColumnKey(period, 'dataDialogs_openedDialogs')], 'count');
    if (value !== null) {
      sum += value;
      hasValue = true;
    }
  });
  return hasValue ? sum : null;
}

function getWorseRankingEligibleRows(rows, periods) {
  let dialogCounts = rows
    .map(row => getUnifiedRowOpenedDialogs(row, periods))
    .filter(value => value !== null && Number.isFinite(value));
  if (dialogCounts.length === 0) {
    return [];
  }

  let medianDialogs = getAnalyticsMedian(dialogCounts);
  return rows.filter(row => {
    let count = getUnifiedRowOpenedDialogs(row, periods);
    return count !== null && count >= medianDialogs;
  });
}

function buildWorseScoreCohortStats(rows, periods) {
  let stats = {};

  analyticsWorseScoreComponents.forEach(component => {
    let values = rows
      .map(row => averageUnifiedRowMetric(row, component.columnId, component.unit, periods))
      .filter(value => value !== null && Number.isFinite(value));
    let median = getAnalyticsMedian(values);
    let reference = component.kind === 'low'
      ? median
      : component.unit === 'count'
        ? Math.max(median, 1)
        : Math.max(median, 0.01);
    stats[component.id] = { reference: reference || (component.unit === 'count' ? 1 : 0.01) };
  });

  return stats;
}

function computeWorseScoreForRow(row, periods, cohortStats) {
  let weightedSum = 0;
  let totalWeight = 0;
  let breakdown = {};

  analyticsWorseScoreComponents.forEach(component => {
    let rawValue = averageUnifiedRowMetric(row, component.columnId, component.unit, periods);
    let normalized = component.kind === 'low'
      ? normalizeWorseLowValue(rawValue, cohortStats[component.id].reference)
      : normalizeWorseHighValue(rawValue, cohortStats[component.id].reference);

    breakdown[component.id] = rawValue;
    if (normalized === null) {
      return;
    }
    weightedSum += component.weight * normalized;
    totalWeight += component.weight;
  });

  if (totalWeight === 0) {
    return null;
  }

  return {
    score: Math.round((weightedSum / totalWeight) * 1000) / 10,
    breakdown: breakdown
  };
}

function getWorstContractorDealerRanking(config) {
  if (!config || !config.data || !Array.isArray(config.data.rows)) {
    return [];
  }

  let periods = getUnifiedTablePeriods(config);
  let eligibleRows = getWorseRankingEligibleRows(config.data.rows, periods);
  if (eligibleRows.length === 0) {
    return [];
  }

  let cohortStats = buildWorseScoreCohortStats(eligibleRows, periods);

  return eligibleRows
    .map(row => {
      let result = computeWorseScoreForRow(row, periods, cohortStats);
      if (!result) {
        return null;
      }
      return {
        row: row,
        score: result.score,
        breakdown: result.breakdown
      };
    })
    .filter(Boolean)
    .sort((a, b) => b.score - a.score)
    .slice(0, 10);
}

function formatWorseBreakdownValue(componentId, value) {
  if (value === null || value === undefined || !Number.isFinite(value)) {
    return '-';
  }
  if (componentId === 'messagesCount') {
    return Math.round(value * 10) / 10;
  }
  return Math.round(value * 1000) / 1000;
}

function renderContractorDealerWorseView(parent, config) {
  let ranking = getWorstContractorDealerRanking(config);
  if (ranking.length === 0) {
    renderAnalyticsMessage(parent, 'Недостаточно данных для WORSE-10');
    return;
  }

  let worstKeys = new Set(ranking.map(item => item.row.__contractorKey));
  renderContractorDealerMetricCharts(parent, config, worstKeys, ' - WORSE-10');
}

function getWorstContractorDealerKeys(configs) {
  let unifiedConfig = configs.find(config => config.isUnifiedContractorTable);
  if (!unifiedConfig) {
    return new Set();
  }
  return new Set(getWorstContractorDealerRanking(unifiedConfig).map(item => item.row.__contractorKey));
}

function getMostActiveContractorKeys(configs) {
  let unifiedConfig = configs.find(config => config.isUnifiedContractorTable);
  if (!unifiedConfig) {
    return new Set();
  }

  return new Set(unifiedConfig.data.rows
    .map(row => ({
      key: row.__contractorKey,
      value: getUnifiedTableActivityScore(row)
    }))
    .sort((a, b) => b.value - a.value)
    .slice(0, 10)
    .map(item => item.key));
}

function getUnifiedTableColumnNumericSum(row, columnId) {
  let flatValue = Number(row[columnId]);
  if (Number.isFinite(flatValue) && flatValue > 0) {
    return flatValue;
  }

  let sum = 0;
  let hasValue = false;
  let suffix = '_' + columnId;
  Object.keys(row).forEach(key => {
    if (key === columnId || key.endsWith(suffix)) {
      let value = Number(row[key]);
      if (Number.isFinite(value)) {
        sum += value;
        hasValue = true;
      }
    }
  });
  return hasValue ? sum : 0;
}

function getUnifiedTableActivityScore(row) {
  return getUnifiedTableColumnNumericSum(row, 'dataDialogs_openedDialogs')
    || getUnifiedTableColumnNumericSum(row, 'dialogType_order')
    || Number(row.openedDialogs)
    || 0;
}

function getContractorRowsByKeys(rows, contractorKeys) {
  return rows.filter(row => contractorKeys.has(getContractorRankKey(row)));
}

function renderContractorSelectedCharts(parent, configs, selectedContractors) {
  let unifiedConfig = configs.find(config => config.isUnifiedContractorTable);
  if (unifiedConfig) {
    if (shouldRenderContractorDealerSelectedAsSummaries(unifiedConfig, selectedContractors)) {
      renderContractorDealerSelectedSummaries(parent, unifiedConfig, selectedContractors);
    } else {
      renderContractorDealerMetricCharts(
        parent,
        unifiedConfig,
        selectedContractors,
        ' - выбранные'
      );
    }
    return;
  }

  let chartsGrid = createTagHtml('div', 'analytics_contractor_rank_charts');
  let titleSuffix = ' - выбранные';

  configs.forEach(config => {
    if (!config.sourceRows || config.sourceRows.length === 0) {
      return;
    }

    let selectedRows = getSelectedContractorRows(config.sourceRows, selectedContractors);
    if (selectedRows.length === 0) {
      return;
    }

    getChartMetricFields({
      title: config.title,
      unit: config.unit,
      fields: config.fields
    }).forEach(fieldGroup => {
      let chartData = buildMetricChartData(selectedRows, fieldGroup.fields, config.unit);
      if (chartData.datasets.length > 0) {
        renderAnalyticsChart(chartsGrid, {
          title: fieldGroup.title + titleSuffix,
          type: getMetricChartType(selectedRows),
          data: chartData,
          unit: config.unit,
          canToggleLabels: hasUser(selectedRows)
        });
      }
    });
  });

  parent.appendChild(chartsGrid);
}

function getSelectedContractorRows(rows, selectedContractors) {
  return rows.filter((row, index) => {
    return selectedContractors.has(getContractorTableKey(row, index));
  });
}

function getRankedContractorRows(rows, field, unit, mode, isDialogType) {
  let scores = {};
  rows.forEach(row => {
    let key = getContractorRankKey(row);
    let value = isDialogType ? Number(row.total) : prepareChartValue(row[field.key], unit);
    if (!Number.isFinite(value)) {
      return;
    }
    if (!scores[key]) {
      scores[key] = {
        key: key,
        value: 0
      };
    }
    scores[key].value += value;
  });

  let rankedKeys = Object.values(scores)
    .sort((a, b) => mode === 'best' ? a.value - b.value : b.value - a.value)
    .slice(0, 10)
    .map(item => item.key);
  let allowedKeys = new Set(rankedKeys);

  return rows.filter(row => allowedKeys.has(getContractorRankKey(row)));
}

function getContractorRankKey(row) {
  if (row.userId) {
    return 'user_' + row.userId;
  }
  return getUserLabel(row);
}

function renderAnalyticsTableContent(parent, config, options = {}) {
  let tableWrap = createTagHtml('div', 'analytics_table_wrap');

  let table = createTagHtml('table', 'analytics_table');

  let thead = createTagHtml('thead');
  let headRow = createTagHtml('tr');
  let sortState = { key: null, direction: 'asc' };
  if (options.selectable) {
    let th = createTagHtml('th');
    th.textContent = 'Выбор';
    headRow.appendChild(th);
  }
  config.data.columns.forEach(column => {
    let th = createTagHtml('th');
    let button = createTagHtml('button', 'analytics_table_sort', '', '', 'button');
    button.dataset.key = column.key;
    button.textContent = column.label;
    button.addEventListener('click', () => {
      sortState.direction = sortState.key === column.key && sortState.direction === 'asc' ? 'desc' : 'asc';
      sortState.key = column.key;
      updateTableSortHeaders(headRow, column.key, sortState.direction);
      renderAnalyticsTableRows(tbody, config.data.columns, sortTableRows(config.data.rows, column.key, sortState.direction), options);
    });
    th.appendChild(button);
    headRow.appendChild(th);
  });
  massAppendChild(thead, headRow);

  let tbody = createTagHtml('tbody');
  renderAnalyticsTableRows(tbody, config.data.columns, config.data.rows, options);

  massAppendChild(table, thead, tbody);
  massAppendChild(tableWrap, table);
  massAppendChild(parent, tableWrap);
}

function renderAnalyticsTableRows(tbody, columns, rows, options = {}) {
  tbody.innerHTML = '';
  rows.forEach(row => {
    let tr = createTagHtml('tr');
    if (options.selectable) {
      let td = createTagHtml('td');
      let checkbox = createTagHtml('input', 'analytics_contractor_checkbox', '', '', 'checkbox');
      checkbox.checked = options.selectedContractors.has(row.__contractorKey);
      checkbox.addEventListener('change', () => {
        if (checkbox.checked) {
          options.selectedContractors.add(row.__contractorKey);
        } else {
          options.selectedContractors.delete(row.__contractorKey);
        }
        if (options.onSelectionChange) {
          options.onSelectionChange();
        }
      });
      massAppendChild(td, checkbox);
      massAppendChild(tr, td);
    }
    columns.forEach(column => {
      let td = createTagHtml('td');
      let cellValue = row[column.key] !== undefined && row[column.key] !== null ? row[column.key] : '-';
      if (column.key === 'details' && options.dealerDependentsByDealer) {
        let hasDetailsText = cellValue !== '-' && cellValue !== '';
        if (hasDetailsText) {
          td.appendChild(createAnalyticsDetailsTextElement(row, cellValue, options));
        } else {
          td.textContent = '-';
        }
      } else {
        td.textContent = cellValue;
      }
      tr.appendChild(td);
    });
    tbody.appendChild(tr);
  });
}

function sortTableRows(rows, key, direction) {
  return rows.slice().sort((a, b) => {
    let first = getTableSortValue(a[key]);
    let second = getTableSortValue(b[key]);
    if (first > second) {
      return direction === 'asc' ? 1 : -1;
    }
    if (first < second) {
      return direction === 'asc' ? -1 : 1;
    }
    return 0;
  });
}

function getTableSortValue(value) {
  if (value === undefined || value === null || value === '-') {
    return '';
  }
  if (typeof value === 'string' && /\s*(д|ч|мин)\./i.test(value)) {
    let durationSeconds = parseDurationSeconds(value);
    if (durationSeconds !== null) {
      return durationSeconds;
    }
  }
  let stringValue = String(value).replace(',', '.').replace(/\s/g, '');
  let numericValue = parseFloat(stringValue);
  if (Number.isFinite(numericValue) && /[0-9]/.test(stringValue)) {
    return numericValue;
  }
  return String(value).toLowerCase();
}

function updateTableSortHeaders(headRow, activeKey, direction) {
  headRow.querySelectorAll('.analytics_table_sort').forEach(button => {
    button.classList.remove('analytics_table_sort_active');
    button.removeAttribute('data-sort-direction');
    if (button.dataset.key === activeKey) {
      button.classList.add('analytics_table_sort_active');
      button.setAttribute('data-sort-direction', direction);
    }
  });
}

function renderChartLabelToggles(sourceData) {
  let toggleItems = getChartToggleItems(sourceData);
  let togglesBlock = createTagHtml('div', 'analytics_chart_toggles');

  toggleItems.forEach(item => {
    let toggle = createTagHtml('button', 'analytics_chart_toggle', '', '', 'button');
    toggle.dataset.entityKey = item.key;
    if (!analyticsHiddenEntityKeys.has(item.key)) {
      toggle.classList.add('analytics_chart_toggle_active');
    }
    toggle.textContent = item.label;
    toggle.addEventListener('click', () => {
      if (analyticsHiddenEntityKeys.has(item.key)) {
        analyticsHiddenEntityKeys.delete(item.key);
      } else {
        analyticsHiddenEntityKeys.add(item.key);
      }
      updateChartToggleButtons(item.key);
      refreshAnalyticsChartsVisibility();
    });
    togglesBlock.appendChild(toggle);
  });

  return togglesBlock;
}

function cloneChartData(chartData) {
  return {
    labels: chartData.labels.slice(),
    labelEntityKeys: Array.isArray(chartData.labelEntityKeys) ? chartData.labelEntityKeys.slice() : null,
    datasets: chartData.datasets.map(dataset => {
      let clonedDataset = Object.assign({}, dataset);
      clonedDataset.data = dataset.data.slice();
      if (Array.isArray(dataset.backgroundColor)) {
        clonedDataset.backgroundColor = dataset.backgroundColor.slice();
      }
      if (Array.isArray(dataset.borderColor)) {
        clonedDataset.borderColor = dataset.borderColor.slice();
      }
      return clonedDataset;
    })
  };
}

function getFilteredChartData(sourceData) {
  let visibleIndexes = sourceData.labels
    .map((label, index) => {
      if (!Array.isArray(sourceData.labelEntityKeys)) {
        return index;
      }
      return analyticsHiddenEntityKeys.has(sourceData.labelEntityKeys[index]) ? null : index;
    })
    .filter(index => index !== null);

  let datasets = sourceData.datasets
    .filter(dataset => !dataset.entityKey || !analyticsHiddenEntityKeys.has(dataset.entityKey))
    .map(dataset => {
      let updatedDataset = Object.assign({}, dataset);
      updatedDataset.data = visibleIndexes.map(index => dataset.data[index]);
      if (Array.isArray(dataset.backgroundColor)) {
        updatedDataset.backgroundColor = visibleIndexes.map(index => dataset.backgroundColor[index]);
      }
      if (Array.isArray(dataset.borderColor)) {
        updatedDataset.borderColor = visibleIndexes.map(index => dataset.borderColor[index]);
      }
      return updatedDataset;
    });

  return {
    labels: visibleIndexes.map(index => sourceData.labels[index]),
    labelEntityKeys: Array.isArray(sourceData.labelEntityKeys) ? visibleIndexes.map(index => sourceData.labelEntityKeys[index]) : null,
    datasets: datasets
  };
}

function refreshAnalyticsChartsVisibility() {
  analyticsChartStates.forEach(state => {
    state.chart.data = getFilteredChartData(state.sourceData);
    state.chart.update();
  });
}

function updateChartToggleButtons(entityKey) {
  document.querySelectorAll('.analytics_chart_toggle').forEach(button => {
    if (button.dataset.entityKey !== entityKey) {
      return;
    }
    if (analyticsHiddenEntityKeys.has(entityKey)) {
      button.classList.remove('analytics_chart_toggle_active');
    } else {
      button.classList.add('analytics_chart_toggle_active');
    }
  });
}

function getChartToggleItems(sourceData) {
  if (Array.isArray(sourceData.labelEntityKeys)) {
    return sourceData.labels.map((label, index) => ({
      key: sourceData.labelEntityKeys[index],
      label: label
    }));
  }

  let items = [];
  let usedKeys = new Set();
  sourceData.datasets.forEach(dataset => {
    if (!dataset.entityKey || usedKeys.has(dataset.entityKey)) {
      return;
    }
    usedKeys.add(dataset.entityKey);
    items.push({
      key: dataset.entityKey,
      label: getDatasetEntityLabel(dataset.label)
    });
  });
  return items;
}

function getDatasetEntityLabel(label) {
  return String(label || '').split(' - ')[0];
}

function renderDialogTypeStats(stats) {
  let statsBlock = createTagHtml('div', 'analytics_dialog_type_stats');

  stats.forEach(item => {
    let row = createTagHtml('div', 'analytics_dialog_type_stat');

    let marker = createTagHtml('span', 'analytics_dialog_type_marker');
    marker.style.backgroundColor = getAnalyticsColor(item.colorIndex, 0.8);

    let text = createTagHtml('div', 'analytics_dialog_type_text');

    let label = createTagHtml('div', 'analytics_dialog_type_label');
    label.textContent = item.label;

    let description = createTagHtml('div', 'analytics_dialog_type_description');
    description.textContent = item.description;

    let value = createTagHtml('div', 'analytics_dialog_type_value');
    value.textContent = formatAnalyticsValue(item.total, 'count') + ' (' + item.percent + '%)';

    massAppendChild(text, label, description);
    massAppendChild(row, marker, text, value);
    massAppendChild(statsBlock, row);
  });

  return statsBlock;
}

function renderAnalyticsSummary(parent, config) {
  let card = createTagHtml('div', 'analytics_chart_card');
  card.classList.add('analytics_summary_card');

  let title = createTagHtml('h3', 'analytics_chart_title');
  title.textContent = config.title;
  card.appendChild(title);

  let valuesBlock = createTagHtml('div', 'analytics_summary_values');

  config.data.items.forEach(item => {
    let itemBlock = createTagHtml('div', 'analytics_summary_item');

    let label = createTagHtml('div', 'analytics_summary_label');
    label.textContent = item.label;

    let value = createTagHtml('div', 'analytics_summary_value');
    value.textContent = formatAnalyticsValue(item.value, config.unit);

    massAppendChild(itemBlock, label, value);
    massAppendChild(valuesBlock, itemBlock);
  });

  massAppendChild(card, valuesBlock);
  massAppendChild(parent, card);
}

function getAnalyticsChartOptions(config) {
  let isDoughnut = config.type === 'doughnut';
  return {
    indexAxis: config.indexAxis || 'x',
    responsive: true,
    maintainAspectRatio: false,
    interaction: {
      mode: 'index',
      intersect: false
    },
    plugins: {
      legend: {
        position: isDoughnut ? 'right' : 'top',
        onClick: function (event, legendItem, legend) {
          let dataset = legend.chart.data.datasets[legendItem.datasetIndex];
          if (!dataset || !dataset.entityKey) {
            if (Chart.defaults.plugins.legend.onClick) {
              Chart.defaults.plugins.legend.onClick(event, legendItem, legend);
            }
            return;
          }
          if (analyticsHiddenEntityKeys.has(dataset.entityKey)) {
            analyticsHiddenEntityKeys.delete(dataset.entityKey);
          } else {
            analyticsHiddenEntityKeys.add(dataset.entityKey);
          }
          updateChartToggleButtons(dataset.entityKey);
          refreshAnalyticsChartsVisibility();
        }
      },
      tooltip: {
        callbacks: {
          label: function (context) {
            let label = context.dataset.label ? context.dataset.label + ': ' : '';
            let durationSeconds = context.dataset.durationSeconds;
            let tooltipValue;
            if (isDurationUnit(config.unit) && Array.isArray(durationSeconds)) {
              let seconds = durationSeconds[context.dataIndex];
              tooltipValue = seconds === null || seconds === undefined
                ? '-'
                : formatDurationSeconds(seconds);
            } else {
              let value = getTooltipParsedValue(context, config);
              tooltipValue = isDurationUnit(config.unit)
                ? formatDurationFromChartValue(value)
                : formatAnalyticsValue(value, config.unit);
            }
            let tooltip = label + tooltipValue;
            if (config.showDialogPercent) {
              tooltip += ' (' + getTooltipPercent(context) + '%)';
            }
            return tooltip;
          }
        }
      }
    },
    scales: isDoughnut ? {} : {
      x: {
        stacked: Boolean(config.stacked),
        beginAtZero: config.indexAxis === 'y',
        ticks: {
          autoSkip: false,
          maxRotation: 45,
          minRotation: 0,
          callback: function (value) {
            if (config.indexAxis === 'y') {
              return isDurationUnit(config.unit)
                ? formatDurationFromChartValue(value)
                : formatAnalyticsValue(value, config.unit);
            }
            return this.getLabelForValue(value);
          }
        }
      },
      y: {
        stacked: Boolean(config.stacked),
        beginAtZero: true,
        ticks: {
          autoSkip: false,
          callback: function (value) {
            if (config.indexAxis === 'y') {
              return this.getLabelForValue(value);
            }
            return isDurationUnit(config.unit)
              ? formatDurationFromChartValue(value)
              : formatAnalyticsValue(value, config.unit);
          }
        }
      }
    }
  };
}

function getTooltipPercent(context) {
  let dataIndex = context.dataIndex;
  let datasets = context.chart.data.datasets;
  let value = Number(context.raw) || 0;
  let total = 0;

  if (datasets.length > 1) {
    total = datasets.reduce((sum, dataset) => {
      return sum + (Number(dataset.data[dataIndex]) || 0);
    }, 0);
  } else {
    total = datasets[0].data.reduce((sum, item) => sum + (Number(item) || 0), 0);
  }

  if (!total) {
    return 0;
  }
  return Math.round((value / total) * 1000) / 10;
}

function getTooltipParsedValue(context, config) {
  if (config.type === 'doughnut') {
    return context.parsed;
  }
  if (config.indexAxis === 'y') {
    return context.parsed.x;
  }
  return context.parsed.y;
}

function getMetricChartType(rows) {
  return hasComparablePeriod(rows) ? 'line' : 'bar';
}

function hasPeriod(rows) {
  return rows.some(row => Boolean(row.period));
}

function hasUser(rows) {
  return rows.some(row => Boolean(getAnalyticsRowUserId(row) || row.userId || row.user));
}

function getPeriodLabel(row) {
  return row.period ? String(row.period) : 'Итого';
}

function getRowLabel(row, index) {
  if (row.user || row.userId) {
    return getUserLabel(row);
  }
  if (row.typeDialog) {
    return getDialogTypeInfo(row.typeDialog).label;
  }
  return index === 0 ? 'Итого' : 'Значение ' + (index + 1);
}

function getAnalyticsUserDisplayName(user) {
  if (!user || typeof user !== 'object') {
    return '';
  }

  if (typeof user.displayName === 'string' && user.displayName.trim()) {
    return user.displayName.trim();
  }

  let firstName = user.firstname || user.firstName || '';
  let lastName = user.lastname || user.lastName || '';
  let combinedName = [firstName, lastName].filter(Boolean).join(' ').trim();
  if (combinedName) {
    return combinedName;
  }

  if (typeof user.name === 'string' && user.name.trim()) {
    if (typeof user.lastname === 'string' && user.lastname.trim() && user.name.indexOf(user.lastname) === -1) {
      return (user.name + ' ' + user.lastname).trim();
    }
    return user.name.trim();
  }

  if (user.firmName) {
    return String(user.firmName).trim();
  }
  if (user.dealerName) {
    return String(user.dealerName).trim();
  }
  return '';
}

function getUserLabel(row) {
  if (row.user) {
    let attachedName = getAnalyticsUserDisplayName(row.user);
    if (attachedName) {
      return attachedName;
    }
  }

  let idUser = getAnalyticsRowUserId(row) || row.userId || null;
  if (idUser) {
    let catalogUser = lookupAnalyticsUser(analyticsUsersCatalog, idUser);
    let catalogName = getAnalyticsUserDisplayName(catalogUser);
    if (catalogName) {
      return catalogName;
    }
    return 'ID ' + idUser;
  }

  return 'Пользователь';
}

function getUserColorKey(row) {
  if (row.userId) {
    return 'user_' + row.userId;
  }
  if (row.user && row.user.id) {
    return 'user_' + row.user.id;
  }
  return getUserLabel(row);
}

function getUniqueUserItems(rows) {
  let users = [];
  let usedKeys = new Set();
  rows.forEach(row => {
    let key = getUserColorKey(row);
    if (!usedKeys.has(key)) {
      users.push({
        key: key,
        label: getUserLabel(row)
      });
      usedKeys.add(key);
    }
  });
  return users;
}

function getAnalyticsUserColumnLabel() {
  if (analyticsWhoseMode === 'factory') {
    return 'Менеджер';
  }
  if (analyticsWhoseMode === 'contractor') {
    return 'Контрагент';
  }
  if (analyticsWhoseMode === 'dealer') {
    return 'Дилер';
  }
  if (analyticsWhoseMode === 'dealer_dependents') {
    return 'Контрагент';
  }
  return 'Пользователь';
}

function getAnalyticsUsersTableTitle() {
  if (analyticsWhoseMode === 'factory') {
    return 'Менеджеры';
  }
  if (analyticsWhoseMode === 'contractor') {
    return 'Контрагенты';
  }
  if (analyticsWhoseMode === 'dealer') {
    return 'Дилеры';
  }
  if (analyticsWhoseMode === 'dealer_dependents') {
    return 'Контрагенты дилера';
  }
  return 'Пользователи';
}

function getUserDetails(row, index) {
  if (!row.user) {
    let idUser = getAnalyticsRowUserId(row) || row.userId || null;
    if (idUser) {
      let catalogUser = lookupAnalyticsUser(analyticsUsersCatalog, idUser);
      let catalogName = getAnalyticsUserDisplayName(catalogUser);
      if (catalogName) {
        return catalogName;
      }
      return 'ID ' + idUser;
    }
    return 'Строка ' + (index + 1);
  }

  if (row.user.dealerName) {
    return 'Дилер: ' + row.user.dealerName;
  }
  if (row.user.distributorName) {
    return 'Дистрибьютор ';
  }
  if (row.user.pointName) {
    return 'Дизайнер дилера: ' + row.user.pointName;
  }
  return '-';
}

function getDialogTypeInfo(typeDialog) {
  let key = normalizeDialogType(typeDialog);
  let labels = {
    order: {
      key: 'order',
      label: 'Заказы',
      description: 'Диалоги, связанные с заказами',
      colorIndex: 0
    },
    complaint: {
      key: 'complaint',
      label: 'Рекламации',
      description: 'Обращения по проблемам и претензиям',
      colorIndex: 1
    },
    consultation: {
      key: 'consultation',
      label: 'Консультации',
      description: 'Общие вопросы и консультации',
      colorIndex: 2
    }
  };
  return labels[key] || {
    key: key || 'unknown',
    label: formatUnknownDialogType(typeDialog),
    description: 'Другой тип диалога',
    colorIndex: 4
  };
}

function normalizeDialogType(typeDialog) {
  return String(typeDialog || '')
    .trim()
    .replace(/[\s_-]+/g, '')
    .toLowerCase();
}

function formatUnknownDialogType(typeDialog) {
  if (!typeDialog) {
    return 'Тип не указан';
  }
  return String(typeDialog)
    .replace(/[_-]+/g, ' ')
    .replace(/([a-z])([A-Z])/g, '$1 $2')
    .trim()
    .replace(/\s+/g, ' ')
    .replace(/^./, letter => letter.toUpperCase());
}

function prepareChartValue(value, unit) {
  let numberValue = Number(value);
  if (!Number.isFinite(numberValue)) {
    return null;
  }
  if (isDurationUnit(unit)) {
    return getChartDurationValue(numberValue);
  }
  if (unit === 'ratio') {
    return Math.round(numberValue * 1000) / 1000;
  }
  if (unit === 'percent') {
    return Math.round(numberValue * 10) / 10;
  }
  return Math.round(numberValue * 100) / 100;
}

function formatAnalyticsValue(value, unit) {
  if (isDurationUnit(unit)) {
    return formatDurationSeconds(value);
  }
  let numberValue = Number(value);
  if (!Number.isFinite(numberValue)) {
    return '-';
  }
  if (unit === 'percent') {
    return (Math.round(numberValue * 10) / 10) + ' %';
  }
  return numberValue;
}

function uniqueValues(values) {
  return values.filter((value, index, array) => value && array.indexOf(value) === index);
}

function getAnalyticsColor(index, alpha) {
  let colors = [
    '31, 119, 180',
    '255, 127, 14',
    '44, 160, 44',
    '214, 39, 40',
    '148, 103, 189',
    '140, 86, 75',
    '227, 119, 194',
    '127, 127, 127',
    '188, 189, 34',
    '23, 190, 207',
    '255, 187, 120',
    '57, 59, 121',
    '152, 223, 138',
    '197, 176, 213',
    '219, 219, 141',
    '199, 199, 199'
  ];
  return 'rgba(' + colors[Math.abs(index) % colors.length] + ', ' + alpha + ')';
}

function getStableEntityColor(value, alpha) {
  let hue = Math.round((getStableColorIndex(value) * 137.508) % 360);
  return 'hsla(' + hue + ', 72%, 42%, ' + alpha + ')';
}

function getStableColorIndex(value) {
  let hash = 0;
  let stringValue = String(value || '');
  for (let i = 0; i < stringValue.length; i++) {
    hash = ((hash << 5) - hash) + stringValue.charCodeAt(i);
    hash = hash & hash;
  }
  return Math.abs(hash);
}

function headerAnalyticsController() {
  let analyticsHeader = document.querySelector('.analytics_header');
  let whoseTitle = analyticsHeader.querySelector('.whose_analytics_title');
  let whoseSelect = analyticsHeader.querySelectorAll('.whose_analytics_select_option');
  let dialoguesTypeTitle = analyticsHeader.querySelector('.dialogues_type_analytics_title');
  let dialoguesTypeSelect = analyticsHeader.querySelectorAll('.dialogues_type_analytics_select_option');
  let userSearch = analyticsHeader.querySelector('.user_search');
  let userSearchInput = analyticsHeader.querySelector('.input_user_search');
  let periodTitle = analyticsHeader.querySelector('.period_analytics_title');
  let periodSelect = analyticsHeader.querySelectorAll('.period_analytics_select_option');
  let timeModeTitle = analyticsHeader.querySelector('.time_mode_analytics_title');
  let timeModeSelect = analyticsHeader.querySelectorAll('.time_mode_analytics_select_option');
  replaceTitle(whoseSelect, whoseTitle);
  replaceTitle(dialoguesTypeSelect, dialoguesTypeTitle);
  replaceTitle(periodSelect, periodTitle);
  replaceTitle(timeModeSelect, timeModeTitle);
  sendFilterAnalytics(whoseSelect, 'whose');
  sendFilterAnalytics(dialoguesTypeSelect, 'dialoguesType');
  sendFilterAnalytics(periodSelect, 'period');
  sendFilterAnalytics(timeModeSelect, 'timeMode');
  initAnalyticsSlaControls();
  showUserSearch(whoseSelect, userSearch);
  sendUserSearch(userSearchInput);
  syncAnalyticsUserSearchVisibility(analyticsWhoseMode, userSearch, userSearchInput);
}

function replaceTitle(arrayOptions, title) {
  arrayOptions.forEach(element => {
    element.addEventListener('click', () => {
      title.textContent = element.textContent;
    });
  });
}

function sendFilterAnalytics(arrayOptions, whatFilter) {
  arrayOptions.forEach(element => {
    element.addEventListener('click', () => {
      let value = element.getAttribute('data-value');
      let method = 'setAnalytics' + toCapsCase(whatFilter);
      if (whatFilter === 'whose') {
        analyticsWhoseMode = value || 'all';
        resetAnalyticsUserSearch();
      }
      if (whatFilter === 'timeMode') {
        analyticsTimeMode = value || 'work_hours';
      }
      simpleSendData(value, method);
      sendShowWhere(where);
    });
  });
}

function initAnalyticsSlaControls() {
  if (!document.getElementById('sla_first_days') || !document.getElementById('sla_subsequent_days')) {
    return;
  }

  setSlaDurationInputs('first', analyticsSlaFirstMinutes);
  setSlaDurationInputs('subsequent', analyticsSlaSubsequentMinutes);

  function scheduleSlaRefetch(target, method, fallbackMinutes, setter) {
    clearTimeout(analyticsSlaDebounceTimer);
    analyticsSlaDebounceTimer = setTimeout(() => {
      let minutes = normalizeSlaMinutes(readSlaDurationMinutes(target), fallbackMinutes);
      setter(minutes);
      setSlaDurationInputs(target, minutes);
      simpleSendData(String(minutes), method);
      sendShowWhere(where);
    }, 350);
  }

  ['first', 'subsequent'].forEach(target => {
    ['days', 'hours', 'minutes'].forEach(part => {
      let input = document.getElementById('sla_' + target + '_' + part);
      if (!input) {
        return;
      }
      input.addEventListener('input', () => {
        if (target === 'first') {
          scheduleSlaRefetch('first', 'setAnalyticsSlaFirstResponse', 180, value => {
            analyticsSlaFirstMinutes = value;
          });
          return;
        }
        scheduleSlaRefetch('subsequent', 'setAnalyticsSlaSubsequentResponse', 120, value => {
          analyticsSlaSubsequentMinutes = value;
        });
      });
    });
  });

  document.querySelectorAll('.sla_analytics_preset').forEach(button => {
    button.addEventListener('click', () => {
      let minutes = Number(button.getAttribute('data-minutes'));
      let target = button.getAttribute('data-target');
      if (!Number.isFinite(minutes) || minutes <= 0 || !target) {
        return;
      }
      minutes = normalizeSlaMinutes(minutes, target === 'first' ? 180 : 120);
      if (target === 'first') {
        analyticsSlaFirstMinutes = minutes;
        setSlaDurationInputs('first', minutes);
        simpleSendData(String(minutes), 'setAnalyticsSlaFirstResponse');
      } else {
        analyticsSlaSubsequentMinutes = minutes;
        setSlaDurationInputs('subsequent', minutes);
        simpleSendData(String(minutes), 'setAnalyticsSlaSubsequentResponse');
      }
      sendShowWhere(where);
    });
  });
}

function resetAnalyticsUserSearch() {
  let userSearch = document.querySelector('.user_search');
  if (!userSearch) {
    return;
  }
  let userSearchInput = userSearch.querySelector('.input_user_search');
  let searchResultBlock = userSearch.querySelector('.user_search_result');
  if (userSearchInput) {
    userSearchInput.value = '';
  }
  if (searchResultBlock) {
    searchResultBlock.remove();
  }
}

function syncAnalyticsUserSearchVisibility(whoseMode, searchContainer, searchInput) {
  if (!searchContainer || !searchInput) {
    return;
  }
  if (whoseMode === 'factory') {
    searchContainer.style.display = 'block';
    searchInput.setAttribute('placeholder', 'Введите имя менеджера');
    return;
  }
  if (whoseMode === 'contractor') {
    searchContainer.style.display = 'block';
    searchInput.setAttribute('placeholder', 'Введите имя контрагента');
    return;
  }
  if (whoseMode === 'dealer') {
    searchContainer.style.display = 'block';
    searchInput.setAttribute('placeholder', 'Введите имя дилера');
    return;
  }
  searchContainer.style.display = 'none';
}

function showUserSearch(arrayOptions, parrentElement) {
  let userSearch = parrentElement.querySelector('.input_user_search');
  arrayOptions.forEach(element => {
    element.addEventListener('click', () => {
      let value = element.getAttribute('data-value');
      analyticsWhoseMode = value || analyticsWhoseMode;
      syncAnalyticsUserSearchVisibility(value, parrentElement, userSearch);
    });
  });
  userSearch.addEventListener('click', () => {
    userSearch.value = '';
    simpleSendData('', 'setAnalyticsUser');
    simpleSendData('', 'getAnalyticsUserSearch');
  });
  userSearch.addEventListener('blur', () => {
    let searchResultBlock = document.querySelector('.user_search_result');
    if (searchResultBlock) {
      searchResultBlock.remove();
    }
  });
}

function sendUserSearch(searchInput) {
  searchInput.addEventListener('input', () => {
    let value = searchInput.value;
    simpleSendData(value, 'getAnalyticsUserSearch');
  });
}


function analyticsUserSearch(data) {
  const dataArr = Object.values(data);
  dataArr.forEach(item => registerAnalyticsUser(analyticsUsersCatalog, item, item && (item.id || item.idUser || item.userId)));
  let searchBlock = document.querySelector('.user_search');
  let searchResultBlock = searchBlock.querySelector('.user_search_result');
  if (!searchResultBlock) {
    searchResultBlock = createTagHtml('div', 'user_search_result');
    searchBlock.appendChild(searchResultBlock);
  }
  searchResultBlock.innerHTML = '';
  if (dataArr.length > 0) {
    dataArr.forEach(item => {
      let searchResultItem = createTagHtml('div', 'user_search_result_item');
      searchResultItem.textContent = item.name + ' (' + item.role + ')';
      searchResultItem.setAttribute('data-value', item.id);
      searchResultItem.addEventListener('mousedown', event => {
        event.preventDefault();
        selectAnalyticsUser(item, searchBlock);
      });
      searchResultBlock.appendChild(searchResultItem);
    });
  } else {
    let searchResultItem = createTagHtml('div', 'user_search_result_item');
    searchResultItem.classList.add('user_search_result_item_not_found');
    searchResultItem.textContent = 'Ничего не найдено';
    searchResultBlock.appendChild(searchResultItem);
  }
}

function selectAnalyticsUser(item, searchBlock) {
  let searchInput = searchBlock.querySelector('.input_user_search');
  let searchResultBlock = searchBlock.querySelector('.user_search_result');
  if (searchInput) {
    searchInput.value = item.name + ' (' + item.role + ')';
  }
  if (searchResultBlock) {
    searchResultBlock.remove();
  }
  simpleSendData(item.id, 'setAnalyticsUser');
  sendShowWhere(where);
}

