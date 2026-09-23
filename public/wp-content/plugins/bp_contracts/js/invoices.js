"use strict";

const where = 'invoices';
// Wait for socket connection before sending data
// setTimeout(() => {
//   // Send initial data only after socket is connected
//   if (socket.readyState === WebSocket.OPEN) {
//     setDefaultState_NEW(where);
//     sendShowWhere(where);
//   }
// }, 1000);

function setDefaultState(where) {
  if (socket.readyState === WebSocket.OPEN) {
    setDefaultState_NEW(where);
    sendShowWhere(where);
  }
}

controlPerPage(where);
startSearch(where);
clickAllCheckBox(where);
fixedHeaderTable(where);

function handlerInvoicesOnPage(data, user) {
  let userParam = user.getAllParam();
  invoicesOnPage(data, userParam);
}

function invoicesOnPage(data, user) {
  try {
    let jsonData = data.reverse();
    let flagUpdate = checkUpdateTable(jsonData, where);
    let countPage;
    jsonData.forEach(element => {
      if (jsonData.length === 1) {
        responseNothingFound(where);
        return;
      }
      if (element.countPage) {
        countPage = element.countPage;
        return;
      }
      updateInvoicesTable(element, user);
    });
    let currentPage = getCurrentPage();
    updatePagination(countPage, currentPage, where);
    paintBackgroundRowInTable(where);
    listenerInvoicesCheckbox();
    // let headerInvoices = document.getElementById('tr_header_invoices');
    // let trInvoices = showNewInvoiceData();
    // headerInvoices.insertAdjacentElement("afterend", trInvoices);
  } catch (error) {
    console.error('Ошибка при парсинге JSON:', error);
  }
}

function updateInvoicesTable(data, user) {
  let targetOrderOnPage = document.querySelector('[data-nf-invoice="' + data.idInvoice + '"]');
  let headerInvoices = document.getElementById('tr_header_invoices');
  let nothingFound = document.getElementById('nothing_found');
  if (nothingFound) {
    nothingFound.remove();
  }
  if (!targetOrderOnPage) {
    let trInvoice = showNewInvoiceData(data, user);
    headerInvoices.insertAdjacentElement("afterend", trInvoice);
  } else {
    // updateOrderRow(data, targetOrderOnPage);
    // targetOrderOnPage.parentNode.removeChild(targetOrderOnPage);
    // headerOrders.insertAdjacentElement("afterend", targetOrderOnPage);
  };
}

function showNewInvoiceData(data, user) {
  data.invoiceSum = Math.round(data.invoiceSum * data.invoiceChange);
  data.paidSum = Math.round(data.paidSum * data.invoiceChange);
  data.debt = data.invoiceSum - data.paidSum;
  let trInvoice = createRowTableInvoices(data, user);
  let tdInvoicesCheckbox = createTagHtml("div", "td_div_table_invoices");
  let checkboxInvoices = createTagHtml("input", "checkbox_invoices", "", "invoice-" + data.idInvoice, "checkbox");
  checkboxInvoices.setAttribute('data-invoice-sum', data.invoiceSum);
  checkboxInvoices.setAttribute('data-paid-sum', data.paidSum);
  checkboxInvoices.setAttribute('data-debt', data.debt);
  checkboxInvoices.setAttribute('data-currency', data.currency);
  tdInvoicesCheckbox.appendChild(checkboxInvoices);

  trInvoice.prepend(tdInvoicesCheckbox);
  return trInvoice;
}

function chooseCurrency(currId) {
  let currency = '';
  switch (currId) {
    case '':
      currency = 'Фирмы нет в таблице Firms';
      break;
    case 0:
      currency = 'EUR';
      break;
    case 1:
      currency = 'BYN';
      break;
    case 2:
      currency = 'RUB';
      break;
  }
  return currency;
}

function createRowTableInvoices(data, user) {
  data.currency = chooseCurrency(data.currId);
  let invoiceSum = data.invoiceSum.toLocaleString('ru-RU');
  let debt = data.debt.toLocaleString('ru-RU');
  let invoiceLink = createTagHtml("a", "invoice_link", data.idInvoice);


  let trInvoice = createTagHtml("div", "tr_div_table_invoices", '', '', '');
  trInvoice.setAttribute('data-nf-' + where, data.idInvoice);
  let invoiceLinkDownload = createTagHtml("a", "invoice_link_download", "Скачать");
  invoiceLinkDownload.classList.add("hidden-link");
  let tdInvoicesFirm = createTagHtml("div", "td_div_table_invoices", data.firmName);
  let tdInvoiceDate = createTagHtml("div", "td_div_table_invoices", formatDate(data.invoiceDate));
  let tdInvoiceNumber = createTagHtml("div", "td_div_table_invoices");

  // massAppendChild(
  //   tdInvoiceNumber,
  //   invoiceLink, invoiceLinkDownload
  // );
  tdInvoiceNumber.appendChild(invoiceLink);
  invoiceDateHover(data.idInvoice, tdInvoiceNumber, invoiceLink, user.role);

  let tdInvoiceAmount = createTagHtml("div", "td_div_table_invoices", invoiceSum + '  ' + data.currency);
  let tdInvoiceDebt = createTagHtml("div", "td_div_table_invoices", debt + '  ' + data.currency);
  massAppendChild(
    trInvoice,
    tdInvoicesFirm, tdInvoiceNumber, tdInvoiceDate, tdInvoiceAmount, tdInvoiceDebt
  );

  invoiceLinkDownload.addEventListener('click', function (event) {
    event.preventDefault();
    simpleSendData(data.idInvoice, 'printInvoice');
  });
  return trInvoice;
}


function listenerInvoicesCheckbox() {
  let checkboxes = document.querySelectorAll('.checkbox_invoices');
  let sum = 0;
  let paid = 0;
  let debt = 0;
  let characteristicsInvoices = document.querySelector('.characteristics-invoices');
  let totalSum = document.querySelector('.total_sum');
  let totalPaid = document.querySelector('.total_paid');
  let totalDebt = document.querySelector('.total_debt');

  checkboxes.forEach(checkbox => {
    checkbox.addEventListener('change', function (event) {
      event.preventDefault();
      let sumExtracted = parseInt(this.getAttribute('data-invoice-sum'));
      let paidExtracted = parseInt(this.getAttribute('data-paid-sum'));
      let debtExtracted = parseInt(this.getAttribute('data-debt'));
      let currency = this.getAttribute('data-currency');
      if (this.checked) {
        sum += sumExtracted;
        paid += paidExtracted;
        debt += debtExtracted;
      } else {
        sum -= sumExtracted;
        paid -= paidExtracted;
        debt -= debtExtracted;
      }
      totalSum.innerHTML = sum.toLocaleString('ru-RU') + ' ' + currency;
      totalPaid.innerHTML = paid.toLocaleString('ru-RU') + ' ' + currency;
      totalDebt.innerHTML = debt.toLocaleString('ru-RU') + ' ' + currency;

      if (sum > 0 || paid > 0 || debt > 0) {
        characteristicsInvoices.style.display = 'block';
      } else {
        characteristicsInvoices.style.display = 'none';
      }
    });
  });
}




