
function handlerPrintInvoicePDF(data, user) {
  let userParam = user.getAllParam();
  printInvoicePDF(data);
}

function printInvoicePDF(data) {
  try {
    // const { firmDetails, geosDetails, invoice, orders } = data;
    console.log(data);
    const firmDetails = data.firmDetails;
    const geosDetails = data.geosDetails;
    const invoice = data.invoice;
    const orders = data.orders;
    const invoiceDate = formatDate(invoice.invoiceDate);
    const invoiceNumber = invoice.idInvoice;
    const invoiceChange = invoice.invoiceChange;
    const vAT = invoice.vAT;
    const vATIncluded = invoice.vATIncluded;

    const agreement = firmDetails.agreement;
    const currId = checkCurrId(firmDetails, firmDetails.currId);
    const firmName = firmDetails.fullFirmName;
    const fullFirmName = firmDetails.fullFirmName;
    const accountDetails = chooseAccountDetails(firmDetails, currId);
    
    const manager = geosDetails.manager;
    const managerPositionParts = geosDetails.managerPosition.split(' по ');
    const managerPositionFirst = managerPositionParts[0];
    const managerPositionSecond = managerPositionParts[1];
    const fullFirmNameGeos = geosDetails.fullFirmName;
    const accountDetailsGeos = chooseAccountDetails(geosDetails, currId);

    let ordersData;
    if (vATIncluded) {
      ordersData = packageOrdersData(orders, invoiceChange, currId, vAT);
    } else {
      ordersData = packageOrdersData(orders, invoiceChange, currId);
    }
    // Generate PDF using pdfmake
    generateInvoicePDF({
      invoiceDate,
      invoiceNumber,
      firmName,
      fullFirmName,
      accountDetails,
      manager,
      managerPositionFirst,
      managerPositionSecond,
      fullFirmNameGeos,
      accountDetailsGeos,
      ordersData,
      currId,
      vATIncluded,
      agreement
    });

  } catch (error) {
    console.error('Ошибка при создании PDF:', error);
  }
}

function packageOrdersData(data, invoiceChange, currId, vAT) {
  let fullPrice = 0;
  let fullPriceWithoutVAT = 0;
  let vATNum = 0;
  let newData = {
    'orders': data,
  }
  let count = 0;
  newData.orders.forEach(element => {
    element.Price = Math.round(parseInt(element.Price) * invoiceChange);
    
    element.Price = element.Price;
    element.vATNum = vAT ? element.Price * vAT / (100 + vAT) : 0;
    element.PriceWithoutVAT = vAT ? element.Price - element.vATNum : element.Price;

    element.Price = element.Price;
    element.PriceWithoutVAT = element.PriceWithoutVAT;
    element.vATNum = element.vATNum;

    fullPrice += element.Price;
    fullPriceWithoutVAT += element.PriceWithoutVAT;
    vATNum += element.vATNum;
    count++;
    element.PriceString = element.Price.toLocaleString('ru-RU');
    element.PriceWithoutVATString = isFloat(element.PriceWithoutVAT) ? element.PriceWithoutVAT.toFixed(2).toLocaleString('ru-RU') : element.PriceWithoutVAT.toLocaleString('ru-RU');
    element.vATStringNum = isFloat(element.vATNum) ? element.vATNum.toFixed(2).toLocaleString('ru-RU') : element.vATNum.toLocaleString('ru-RU');

    element.vATPercent = vAT ? vAT + '%' : '0%';
  });

  newData.fullPrice = fullPrice;
  newData.fullPriceWithoutVAT = fullPriceWithoutVAT;
  newData.vATNum = vATNum;
  newData.count = count;

  newData.fullPriceStringNum = newData.fullPrice.toLocaleString('ru-RU');
  newData.vATStringNum = isFloat(newData.vATNum) ? newData.vATNum.toFixed(2).toLocaleString('ru-RU') : newData.vATNum.toLocaleString('ru-RU');
  newData.fullPriceWithoutVATStringNum = isFloat(newData.fullPriceWithoutVAT) ? newData.fullPriceWithoutVAT.toFixed(2).toLocaleString('ru-RU') : newData.fullPriceWithoutVAT.toLocaleString('ru-RU');

  newData.fullPriceString = amountToRussianWords(newData.fullPrice);
  newData.vATString = amountToRussianWords(newData.vAT, true);
  newData.vATNumString = amountToRussianWords(newData.vATNum);

  newData.vAtForPrint = vAT ? 'Сумма  НДС: ' + newData.vATNumString : 'Ставка НДС: 0%';
  return newData;
}

function generateInvoicePDF(data) {
  // Convert image to base64 for pdfmake
  const img = new Image();
  img.crossOrigin = 'anonymous';

  img.onload = function () {
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');
    canvas.width = img.width;
    canvas.height = img.height;
    ctx.drawImage(img, 0, 0);

    // Try both PNG and JPEG formats
    const base64ImagePNG = canvas.toDataURL('image/png');
    const base64ImageJPEG = canvas.toDataURL('image/jpeg', 0.9);

    // Generate PDF with JPEG image (often more compatible)
    generatePDFWithBase64Image(data);
  };
  img.onerror = function (error) {
    console.error('Failed to load image:', error);
    console.error('Image URL was:', window.location.origin + '/wp-content/themes/wp-diary/images/signandseal.png');
    console.error('Generating PDF without image');
    generatePDFWithoutImage(data);
  };
  img.src = window.location.origin + '/wp-content/themes/wp-diary/images/signandseal.png';
}

function generatePDFWithBase64Image(data) {
  // Create images object for pdfmake
  const images = {
    'signature': 'https://dev.gi.by/wp-content/themes/wp-diary/images/signandseal.png'
  };

  // Define document definition

  const docDefinition = {
    pageSize: 'A4',
    pageMargins: [40, 60, 40, 60],
    images,
    defaultStyle: {
      fontSize: 10,
      font: 'Roboto'
    },
    content: [
      {
        columns: [
          {
            width: '*',
            text: [
              { text: 'Продавец: ' + data.fullFirmNameGeos + '\n', bold: true },
              { text: (data.accountDetailsGeos || 'Не указано') },
            ]
          },
          {
            width: 'auto',
            text: [
              { text: 'Счёт № ' + data.invoiceNumber + '\n', bold: true, fontSize: 14 },
              { text: 'от: ' + data.invoiceDate + '\n', bold: true },
              { text: ' ' + '\n' },
              { text: ' ' + data.agreement + '\n' },
              { text: ' ' },
            ],
            alignment: 'right'
          }
        ],
        margin: [0, 0, 0, 30]
      },
      {
        columns: [
          {
            width: '*',
            text: [
              { text: 'Покупатель:' + data.fullFirmName + '\n', bold: true },
              { text: (data.accountDetails || 'Не указано') },
              { text: ' ' + '\n' },
              { text: ' ' + '\n' },
              { text: 'Страна происхождения Республика Беларусь' + '\n' },
            ]
          }
        ],
        margin: [0, 0, 0, 0]
      },

      // Orders table
      {
        table: {
          headerRows: 1,
          widths: ['auto', '*', 'auto', 'auto', 'auto', 'auto', 'auto', 'auto', 'auto', 'auto'],
          body: [
            [
              { text: '№ \n п/п', style: 'tableHeader' },
              { text: 'Наименование', style: 'tableHeader' },
              { text: 'Заказ', style: 'tableHeader', alignment: 'center' },
              { text: 'Ед. \n изм.', style: 'tableHeader', alignment: 'center' },
              { text: 'Кол-\nво\n това\nра', style: 'tableHeader', alignment: 'center' },
              { text: 'Цена \n изготовит.', style: 'tableHeader', alignment: 'center' },
              { text: 'Сумма', style: 'tableHeader', alignment: 'center' },
              { text: 'Ста\nвка\n НДС,\n %', style: 'tableHeader', alignment: 'center' },
              { text: 'Сум\nма\n НДС,', style: 'tableHeader', alignment: 'center' },
              { text: 'Всего \n с НДС', style: 'tableHeader', alignment: 'center' }
            ],
            ...data.ordersData.orders.filter(order => order.ModelName || order.OrderName).map((order, index) => [
              { text: (index + 1).toString() },
              { text: 'Набор мебели для кухни "' + order.ModelName.charAt(0).toUpperCase() + order.ModelName.slice(1) + '"' || 'Не указано' },
              { text: order.OrderName || 'Не указано' },
              { text: 'шт.' },
              { text: '1' },
              { text: order.PriceWithoutVATString || '0' },
              { text: order.PriceWithoutVATString || '0' },
              { text: order.vATPercent || '0' },
              { text: order.vATStringNum || '0' },
              { text: order.PriceString || '0' }
            ]),
            [
              { text: 'ИТОГО', bold: true, border: [false, false, false, false] },
              { text: '', bold: true, border: [false, false, false, false] },
              { text: '', bold: true, border: [false, false, false, false] },
              { text: '', bold: true, border: [false, false, false, false] },
              { text: data.ordersData.count, bold: true, border: [false, false, false, false] },
              { text: '', bold: true, border: [false, false, false, false] },
              { text: data.ordersData.fullPriceWithoutVATStringNum, bold: true, border: [false, false, false, false] },
              { text: '', bold: true, border: [false, false, false, false] },
              { text: data.ordersData.vATStringNum, bold: true, border: [false, false, false, false] },
              { text: data.ordersData.fullPriceStringNum, bold: true, border: [false, false, false, false] }
            ]
          ]
        },
        margin: [0, 0, 0, 10]
      },
      {
        columns: [
          {
            width: '*',
            text: [
              { text: 'Предоплата 100% в течение 5 банковских дней, согл. п. 2.2 ' + data.agreement + '\n' },
              { text: ' ' + '\n' },
              { text: ' ' + '\n' },
              { text: ' ' + '\n' },
              { text: 'Всего к оплате на сумму с НДС: ' + data.ordersData.fullPriceString + '\n' },
              { text: data.ordersData.vAtForPrint + '\n' },
              { text: ' ' + '\n' },
              { text: ' ' + '\n' },
              { text: ' ' + '\n' },
              { text: ' ' + '\n' },
            ]
          },
        ],


      },
      {
        stack: [
          // Текст с подчеркиванием (нижний слой)
          {
            text: [
              { text: data.managerPositionFirst + '   ________________________   ' + data.manager + '\n' },
              { text: 'по ' + data.managerPositionSecond + '\n' },
              { text: ' ' + '\n' },
            ]
          },

          // Изображение подписи (верхний слой)
          {
            image: 'signature',
            width: 170,
            height: 130,
            margin: [190, -65, 0, 0],
            pageBreak: 'avoid'
          }
        ],
        margin: [0, 0, 0, 30]
      }
    ],
    styles: {
      tableHeader: {
        bold: true,
        fontSize: 10,
        color: 'black',
        fillColor: '#f0f0f0'
      }
    }
  };

  // Generate and download PDF
  pdfMake.createPdf(docDefinition).download('Счет_' + data.invoiceNumber + '_' + data.invoiceDate + '.pdf');
}

