"use strict";
const where = 'orders';
// setTimeout(() => {
//   if (socket.readyState === WebSocket.OPEN) {
//     setDefaultState_NEW(where);
//     setDefaultStateFilter(where);
//     startSearchInvoices(where);
//     sendShowWhere(where);
//   }
// }, 1000);

function setDefaultState(where) {
  if (socket.readyState === WebSocket.OPEN) {
    setDefaultState_NEW(where);
    setDefaultStateFilter(where);
    startSearchInvoices(where);
    sendShowWhere(where);
  }
}

controlPerPage(where);
startSearch(where);
filterOnPage(where);
filterDate(where);
callListenerButtonOLD(where, 'print-report-');
callListenerButtonOLD(where, 'package-info-');
clickAllCheckBox(where);

fixedHeaderTable(where);

function listenerOrdersCheckboxes() {
  let checkboxes = document.querySelectorAll('.checkbox_orders');
  
  let bruto = 0;
  let netto = 0;
  let volume = 0;

  let totalBruto = document.querySelector('.total_bruto');
  let totalNetto = document.querySelector('.total_netto');
  let totalVolume = document.querySelector('.total_volume');
  let characteristicsOrders = document.querySelector('.characteristics-orders');

  checkboxes.forEach(checkbox => {
    checkbox.addEventListener('change', function() {
      let brutoExtracted = parseFloat(this.getAttribute('data-brutto'));
      let nettoExtracted = parseFloat(this.getAttribute('data-netto'));
      let volumeExtracted = parseFloat(this.getAttribute('data-volume'));
      brutoExtracted = brutoExtracted > 0 ? parseFloat(brutoExtracted.toFixed(2)) : 0;
      nettoExtracted = nettoExtracted > 0 ? parseFloat(nettoExtracted.toFixed(2)) : 0;
      volumeExtracted = volumeExtracted > 0 ? parseFloat(volumeExtracted.toFixed(2)) : 0;
      if (this.checked) {
        bruto += brutoExtracted;
        netto += nettoExtracted;
        volume += volumeExtracted;
      } else {
        bruto -= brutoExtracted;
        netto -= nettoExtracted;
        volume -= volumeExtracted;
      }
      totalBruto.innerHTML = bruto > 0 ? bruto.toFixed(2) : 0;
      totalNetto.innerHTML = netto > 0 ? netto.toFixed(2) : 0;
      totalVolume.innerHTML = volume > 0 ? volume.toFixed(2) : 0;
      if (bruto > 0 || netto > 0 || volume > 0) {
        characteristicsOrders.style.display = 'block';
      } else {
        characteristicsOrders.style.display = 'none';
      }
    });
  });
}

function handlerOrdersOnPage(data, user) {
  let userParam = user.getAllParam();
  
  ordersOnPage(data, userParam);
}

function handlerSendDataForContract(data, user) {
  let userParam = user.getAllParam();
  contractForWindow(data, userParam);
}

function handlerPrintReportOrdersXLSX(data, user) {
  printReportOrders(data);
}

function handlerPackageInfoOrdersXLSX(data, user) {
  packageInfoOrdersUP(data);
}

/* START CONTROL XLSX Files */
function packageInfoOrdersUP(data) {
  try {
    // Создаем новый Excel файл
    let namesOrders = [];
    const workbook = new ExcelJS.Workbook();
    const lastColumn = 'F';
    
    Object.keys(data).reverse().forEach(orderNumber => {
      const order = data[orderNumber]; // Получаем объект заказа
      // Формируем лист для заказа.
      const worksheet = workbook.addWorksheet(orderNumber);
      setSetupForWorkSheets(worksheet);
      const columns = ['A', 'B', 'C', 'D', 'E', 'F'];
      const nameColumns = ['№ п/п', '№ места', 'Изделие', 'Кол-во', 'Склад 1', 'Склад 2'];
      const widthColumns = [5.2, 7.3, 86, 5.57, 8, 8];
      const headers = createHeadersXLSX(nameColumns, widthColumns, 1.25);
      worksheet.columns = headers;
      setDefaultSyle(worksheet);
      
      namesOrders.push(orderNumber);
      // Заполняем наш лист.
      let pointName;
      for (let status in order) {
        if (status === 'Точка') {
          pointName = order[status];
          const convinientName = orderNumber + ' - ' + pointName;
          // Update the header text for the third column
          const column = worksheet.getColumn(3);
          column.header = convinientName;
          continue;
        }

        const firstRowNumber = worksheet.lastRow ? worksheet.lastRow.number + 1 : 1; 
        const statusOrder = order[status]; // Получаем состояние заказа
        // const nameRow = 'Заказ: № ' + orderNumber + ' - ' + status;
        // addOrderGroupHeader(worksheet, nameRow);
        let lastMergedRow;
        Object.keys(statusOrder).forEach(item => {
          const itemOrder = statusOrder[item];
          // Добавляем строку с возможным объединением колонок
          lastMergedRow = packageRowXLSX2(worksheet, itemOrder, nameColumns, lastMergedRow);
        });
        // Если было объединение на последнем ряду, завершаем его
        if (lastMergedRow) {
          worksheet.mergeCells(lastMergedRow.start, 2, lastMergedRow.end, 2); // Объединение для "№ места"
          lastMergedRow = null; // Сбросить для новой группы заказов
        }
        workWithBordersXLSX2(worksheet, firstRowNumber, columns, lastColumn);
        addEmptyRow(worksheet, 2);
      }
      
      setStyleForColumn(worksheet, 2);
      worksheet.getColumn(3).alignment = { 
        wrapText: false, 
        vertical: 'middle',
        horizontal: 'left'
      };
      finalPreparationWorksheet(worksheet, columns, lastColumn);
      // Лист заполнен.
    });
    const fileName = createFileName('Отчёт Упаковочные', namesOrders);
    finalPreparationXLSX(workbook, fileName);
  } catch (error) {
    console.error("Ошибка при обработке данных:", error);
  }
}



function printReportOrders(data) {
  try {
    // Создаем новый Excel файл
    let namesOrders = [];
    const workbook = new ExcelJS.Workbook();
    const worksheet = workbook.addWorksheet('Orders Report');
    const lastColumn = 'L';
    // Определение заголовков и данных
    const columns = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L'];
    const nameColumns = ['Номер заказа', 'Номер клиента', 'Дата приема', 'Состояние', 'Дата выпуска', 'Дата отгрузки', 'Точка', 'Брутто', 'Нетто', 'Объем', 'Пароль'];
    const widthColumns = [15, 15, 15, 12, 15, 15, 15, 15, 10, 10, 10, 10];
    const headers = createHeadersXLSX(nameColumns, widthColumns);
    const firstRowNumber = worksheet.lastRow ? worksheet.lastRow.number + 1 : 1; // Запоминаем номер первой 
    // Устанавливаем заголовки колонок
    worksheet.columns = headers;
    // Добавляем данные в таблицу
    data.forEach(item => {
      packageRowXLSX(worksheet, item, nameColumns);
      namesOrders.push(item['Номер заказа']);
    });
    const fileName = createFileName('Краткий отчёт', namesOrders);
    workWithBordersXLSX(worksheet, firstRowNumber, columns, lastColumn);
    finalPreparationWorksheet(worksheet, columns, lastColumn, true);
    finalPreparationXLSX(workbook, fileName);
  } catch (error) {
    console.error("Ошибка при обработке данных:", error);
  }
}

function createFileName(fileName, namesOrders) {
  let count = namesOrders.length;
  let ordersNames;
  for (let i = 0; i < 3 ; i++) {
    if (namesOrders[i] !== undefined) {
      ordersNames = ordersNames ? ordersNames + ', ' + namesOrders[i] : namesOrders[i];
    }
  }
  if (count > 3) {
    let digit = count - 3;
    ordersNames = ordersNames + ' и ещё ' + digit + ' ';
  } else {
    ordersNames = ordersNames + ' ';
  }
  // ordersNames =  count > 3 ? ordersNames + ' и ещё ' + count - 3 + ' ' : ordersNames + ' ';
  const date = new Date();
  let today = formatDateObject(date);
  let nameForFile = ordersNames + 'от ' + today;
  fileName = fileName + 'для (' + nameForFile + ')';
  return fileName;
}

function addEmptyRow(worksheet, count) {
  for (let i = 0; i < count; i++) {
    worksheet.addRow([]);
  }
}

function workWithBordersXLSX(worksheet, firstRowNumber, columns, lastColumn) {
  let lastRowNumber = worksheet.lastRow.number; // Номер последней строки заказа
  // Применяем тонкие границы внутри заказа
  for (let i = firstRowNumber; i <= lastRowNumber; i++) {
    setBordersForRow(worksheet, columns, i, false, false, lastColumn);
  }
  // Устанавливаем границы для первой строки (верхние границы)
  setBordersForRow(worksheet, columns, firstRowNumber, true, false, lastColumn);
  // Устанавливаем границы для последней строки (нижние границы)
  setBordersForRow(worksheet, columns, lastRowNumber, false, true, lastColumn);
}

function setBordersForRow(worksheet, columns, rowNumber, isThickTop = false, isThickBottom = false, lastColumn) {
  columns.forEach(column => {
    const cell = worksheet.getCell(`${column}${rowNumber}`);
    cell.border = {
      top: isThickTop ? { style: 'thick' } : { style: 'thin' },
      bottom: isThickBottom ? { style: 'thick' } : { style: 'thin' },
      left: column === 'A' ? { style: 'thick' } : { style: 'thin' },
      right: column === lastColumn ? { style: 'thick' } : undefined
    };
    cell.alignment = { vertical: 'middle'};
  });
}

function workWithBordersXLSX2(worksheet, firstRowNumber, columns, lastColumn) {
  let lastRowNumber = worksheet.lastRow.number; // Номер последней строки заказа
  // Применяем тонкие границы внутри заказа
  for (let i = firstRowNumber; i <= lastRowNumber; i++) {
    setBordersForRow2(worksheet, columns, i, false, false, lastColumn);
  }
  // Устанавливаем границы для первой строки (верхние границы)
  setBordersForRow2(worksheet, columns, firstRowNumber, true, false, lastColumn);
  // Устанавливаем границы для последней строки (нижние границы)
  setBordersForRow2(worksheet, columns, lastRowNumber, false, true, lastColumn);
}

function setBordersForRow2(worksheet, columns, rowNumber, isThickTop = false, isThickBottom = false, lastColumn) {
  columns.forEach(column => {
    const cell = worksheet.getCell(`${column}${rowNumber}`);
    cell.border = {
      top: isThickTop ? { style: 'thin' } : { style: 'thin' },
      bottom: isThickBottom ? { style: 'thin' } : { style: 'thin' },
      left: column === 'A' ? { style: 'thin' } : { style: 'thin' },
      right: column === lastColumn ? { style: 'thin' } : undefined
    };
    cell.alignment = { vertical: 'middle', horizontal: 'center' };
  });
}

function setDefaultSyle(worksheet){
  worksheet.columns.forEach(column => {
    column.font = { 
      name: 'Arial', // Шрифт
      size: 10,      // Размер
      bold: false    // Жирность (false по умолчанию)
    };
  });
}

function setStyleForColumn(worksheet, numberColumn) {
  worksheet.getColumn(numberColumn).font = {
    name: 'Arial', // Название шрифта
    size: 14,                // Размер шрифта
    bold: true,              // Жирный текст
  };
  worksheet.getColumn(numberColumn).alignment = { 
    wrapText: true, 
    vertical: 'middle',
    horizontal: 'center'
  };
}

function finalPreparationWorksheet(worksheet, columns, lastColumn, thick = false) {
  worksheet.views = [
    { state: 'frozen', ySplit: 1 } // ySplit: 1 означает, что первая строка будет закреплена
  ];
  if (thick) {
    setBordersForRow(worksheet, columns, 1, true, true, lastColumn);
  } else {
    setBordersForRow2(worksheet, columns, 1, true, true, lastColumn);
  }
  
  
  // Применение стиля к заголовкам
  worksheet.getRow(1).font = {  
    name: 'Arial',  // Название шрифта
    bold: true,     // Жирный текст
    size: 10,       // Размер шрифта
  }
  worksheet.getRow(1).alignment = { wrapText: true, vertical: 'middle', horizontal: 'center' };
  
}

function setSetupForWorkSheets(worksheet){
  worksheet.pageSetup = {
    margins: { // Устанавливаем поля в дюймах
      left: 0.45,   // Левое поле
      right: 0.45,  // Правое поле
      top: 0.6,   // Верхнее поле
      bottom: 0.25, // Нижнее поле
      header: 0.2, // Отступ для верхнего колонтитула
      footer: 0.2  // Отступ для нижнего колонтитула
    },
    fitToPage: true, // Масштабировать под страницу
    fitToWidth: 1,   // Уместить в ширину 1 страницы
    fitToHeight: 0   // Уместить в высоту (0 означает неограниченно)
  };
  
  worksheet.headerFooter = {
    differentFirst: false, // Один и тот же колонтитул на всех страницах
    differentOddEven: false,
    oddHeader: '&RСтраница &P из &N' // Нумерация в верхнем правом углу
  };
}

function createHeadersXLSX(nameColumns, widthColumns, adjustmentFactor = 1) {
  const headers = nameColumns.map((columnName, index) => {
    return { 
      header: columnName, 
      key: columnName, 
      width: widthColumns[index] / adjustmentFactor // Корректируем ширину
    };
  });
  return headers;
}

function packageRowXLSX(worksheet, item, nameColumns) {
  const rowData = {}; // Создаем объект для строки
  nameColumns.forEach(columnInner => {
    item[columnInner] = !item[columnInner] ? '-' : item[columnInner];
    rowData[columnInner] = item[columnInner];
  });
  worksheet.addRow(rowData);
}

function packageRowXLSX2(worksheet, item, nameColumns, lastMergedRow) {
  const rowData = {}; // Создаем объект для строки
  const currentRowNumber = worksheet.lastRow ? worksheet.lastRow.number + 1 : 1;
  
  nameColumns.forEach((columnInner, index) => {
    item[columnInner] = !item[columnInner] ? '' : item[columnInner];
    
    // Проверяем колонку "№ места" (по индексу). Предположим, что это вторая колонка, то есть index = 1.
    if (columnInner === '№ места' && index === 1) {
      if (lastMergedRow && lastMergedRow.value === item[columnInner]) {
        // Если значение такое же, как в предыдущей строке, не заполняем его и продолжаем объединение ячеек.
        lastMergedRow.end = currentRowNumber; // Обновляем конечную строку для объединения
      } else {
        // Если новое значение, завершаем предыдущее объединение, если оно было
        if (lastMergedRow) {
          worksheet.mergeCells(lastMergedRow.start, index + 1, lastMergedRow.end, index + 1);
        }
        // Начинаем отслеживать новое значение для объединения
        lastMergedRow = { start: currentRowNumber, end: currentRowNumber, value: item[columnInner] };
      }
    }
    
    rowData[columnInner] = item[columnInner];
  });

  worksheet.addRow(rowData);
  return lastMergedRow; // Возвращаем текущее состояние объединённых ячеек
}

function finalPreparationXLSX(workbook, fileName) {
  // Сохраняем файл
  workbook.xlsx.writeBuffer().then(function (buffer) {
    var blob = new Blob([buffer], { type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" });
    var link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = fileName + '.xlsx';
    link.click();
  });
}
/* END CONTROL XLSX Files */

function ordersOnPage(data, user) {
  try {
    let jsonData = data.reverse();
    let flagUpdate = checkUpdateTable(jsonData, where);
    let countPage;
    jsonData.forEach(element => {
      
      if (jsonData.length === 1) {
        responseNothingFound(where); //Вывели ответ что ничего нет в базе
        return;
      }
      if (element.countPage) {
        countPage = element.countPage;
        return;
      }
      updateOrdersTable(element, user);
    });
    let currentPage = getCurrentPage();
    updatePagination(countPage, currentPage, where);
    paintBackgroundRowInTable(where);
    listenerOrdersCheckboxes();
  } catch (error) {
    console.error('Ошибка при парсинге JSON:', error);
  }
}

function updateOrdersTable(data, user) {
  let targetOrderOnPage = document.querySelector('[data-nf-order-table="' + parseInt(data.id) + '"]');
  let headerOrders = document.getElementById('tr_header_orders');
  let nothingFound = document.getElementById('nothing_found');
  if (nothingFound) {
    nothingFound.remove();
  }
  if (!targetOrderOnPage) {
    let trOrder = showNewOrderDataNew(data, user);
    headerOrders.insertAdjacentElement("afterend", trOrder);
  } else {
    updateOrderRow(data, targetOrderOnPage, user);
    targetOrderOnPage.parentNode.removeChild(targetOrderOnPage);
    headerOrders.insertAdjacentElement("afterend", targetOrderOnPage);
  }
}

function updateOrderRow (data, targetOrderOnPage, user) {
  let tdOrderNumberClient = targetOrderOnPage.querySelector('.number-client');
  let tdOrderStatus = targetOrderOnPage.querySelector('[data-order-status]');
  let tdOrderReceptionDate = targetOrderOnPage.querySelector('[data-order-reception-date]');
  let tdOrderDateInvoice = targetOrderOnPage.querySelector('[data-order-invoice-date]');
  let tdOrderDateOutput = targetOrderOnPage.querySelector('[data-order-output-date]');
  let tdOrderDateShipment = targetOrderOnPage.querySelector('[data-order-shipment-date]');

  tdOrderStatus.innerHTML = '';

  tdOrderStatus = controlStatusOrder(tdOrderStatus, data.status, data.orderStatusDate);

  tdOrderNumberClient.innerHTML = data.clientName;
  tdOrderReceptionDate.innerHTML = formatDate(data.receptionDate);
  // Rebuild invoice cell with hoverable link
  tdOrderDateInvoice.innerHTML = '';
  const invoiceDateSpan = createTagHtml('span', 'invoice-date-text', formatDate(data.invoiceDate));
  tdOrderDateInvoice.appendChild(invoiceDateSpan);
  invoiceDateHover(data.invoiceId, tdOrderDateInvoice, invoiceDateSpan, user.role);
  tdOrderDateOutput.innerHTML = formatDate(data.requiredDate);
  tdOrderDateShipment.innerHTML = formatDate(data.shipmentDate);

  tdOrderStatus.setAttribute("data-order-status-date", data.orderStatusDateTS);
  tdOrderReceptionDate.setAttribute("data-order-reception-date", data.receptionDateTS);
  tdOrderDateInvoice.setAttribute("data-order-invoice-date", data.invoiceDateTS);
  tdOrderDateOutput.setAttribute("data-order-output-date", data.requiredDateTS);
  tdOrderDateShipment.setAttribute("data-order-shipment-date", data.shipmentDateTS);
}

function chechOrderRow(data, targetOrderOnPage) {
  let orderStatus = targetOrderOnPage.querySelector('.td-status-date');
  let prevOrderStatus = orderStatus.getAttribute('data-order-status');
  let prevOrderStatusDate = parseInt(orderStatus.getAttribute('data-order-status-date'));
  let checkChangeDateStatus = prevOrderStatus !== data.status;
  let checkChengeStatus = prevOrderStatusDate !== data.orderStatusDateTS;
  if (checkChangeDateStatus || checkChengeStatus) {
    return true;
  } else {
    return false;
  }
}

function showNewOrderDataNew(data, user) {
  let snForUser = String(data.serialNumber).padStart(6, "0");
  let where = 'order-table';
  let trOrder = createRowTableOrders(where, data, user);
  let tdOrderNumberFactory = trOrder.querySelector('.td_table_order_number');
  

  let volume = data.volume === '-' ? 0 : data.volume;
  let netto = data.netto === '-' ? 0 : data.netto;
  let brutto = data.brutto === '-' ? 0 : data.brutto;
  
  let tdOrderCheckbox = createTagHtml("div", "td_div_table_order");
  let checkboxOrder = createTagHtml("input", "checkbox_orders", "", "order-" + data.id, "checkbox");
  checkboxOrder.setAttribute("data-checkbox-orders", data.id);
  checkboxOrder.setAttribute('data-volume', volume);
  checkboxOrder.setAttribute('data-netto', netto);
  checkboxOrder.setAttribute('data-brutto', brutto);
  checkboxOrder.value = data.id;
  tdOrderCheckbox.appendChild(checkboxOrder);
  trOrder.prepend(tdOrderCheckbox);
  if (data.serialNumber !== '-') {
    let orderNumber = trOrder.querySelector('.order-number');
    let contractLink = createTagHtml("a", "contract_dialog_link", data.nameContract);
    contractLink.setAttribute("data-contract-link-sn", data.serialNumber);

    contractLink.classList.add('hidden-link');
    orderNumber.classList.add('hidden-order-number');
    
    tdOrderNumberFactory.appendChild(contractLink);
    eventListenerCallContractWindow(data, contractLink, user, 'contracts');
  }
  return trOrder;
}




