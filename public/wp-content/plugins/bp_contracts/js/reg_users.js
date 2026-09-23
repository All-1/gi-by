regNewUsers();

function regNewUsers() {
  let idsRole = [
    'distributor', 'free_dealer', 'dealer', 'designer_dealer', 'manager', 'consultant', 'complaint_handler', 'bookkeeper', 'shipment_manager', 'specialist', 'sales_manager', 'administrator', 'designer_architect'
  ];
  let textRoles = [
    'Дистрибьютор', 'Свободный дилер', 'Дилер', 'Дизайнер дилера', 'Менеджер', 'Консультант', 'Специалист по качеству', 'Бухгалтер', 'Менеджер по отгрузкам', 'Узкий специалист', 'Менеджер по продажам', 'Админ', 'Дизайнер-архитектор'
  ];
  let boxRegNewUser = document.getElementById('reg-users');
  let login = createRowRegUser('Логин*', 'input', 'login', 'text');
  let email = createRowRegUser('E-mail*', 'input', 'email', 'text');
  let name = createRowRegUser('Имя*', 'input', 'name', 'text');
  let lastname = createRowRegUser('Фамилия*', 'input', 'lastname', 'text');
  let country = createRowRegUser('Страна', 'input', 'country', 'text');
  let city = createRowRegUser('Город', 'input', 'city', 'text');
  let password = createRowRegUser('Пароль', 'input', 'password', 'text');

  let address = createRowRegUser('Адрес', 'textarea', 'address');
  let phone = createRowRegUser('Телефон', 'textarea', 'phone');
  let legalEntity = createRowRegUser('Юр. лицо', 'textarea', 'legal-entity');

  let notification = createRowRegUser('Отправить пользователю письмо об учётной записи', 'input', 'notification', 'checkbox');

  let role = createRowSelect('Роль', 'role', idsRole, textRoles);

  massAppendChild(
    boxRegNewUser,
    login, email, name, lastname,
    country, city, address, phone,
    legalEntity, role, password,
    notification
  );

  let searchBind = createRowRegUser('Привязать к', 'input', 'search-bind', 'search');

  let select = role.querySelector('.select-role');
  checkInclude(['distributor', 'free_dealer', 'dealer', 'designer_dealer'], select, searchBind, ['Имя диллера/дистрибьютера УП', 'Имя точки']);

}

function createRowRegUser(text, tag, name, type) {
  let row = createTagHtml('div', 'row-reg-new-user');
  let textTag = createTagHtml('div', 'text-' + name, text);
  let inputTag = !type ? createTagHtml(tag, tag + '-' + name) : createTagHtml(tag, tag + '-' + name, '', '', type);
  massAppendChild(
    row,
    textTag, inputTag
  );
  return row;
}

function createRowSelect(text, name, ids, texts) {
  let row = createRowRegUser(text, 'div', name);
  let divDisplay = createTagHtml('div', 'select-display', ' - ');
  let ul = createTagHtml('ul', 'select-' + name);
  for (let i = 0; ids.length > i; i++) {
    let li = createTagHtml('li', '', texts[i], ids[i]);
    ul.appendChild(li);
  };
  let container = row.querySelector('.div-' + name);

  massAppendChild(
    container,
    divDisplay, ul
  );
  return row;
}

function checkInclude(conditions, select, block, listText) {
  let allChildren = Array.from(select.children);
  let neededChildren = [];
  allChildren.forEach(child => {
    if (conditions.includes(child.id)) {
      neededChildren.push(child);
    }
    child.addEventListener('click', function () {

      let parrentSelect = select.parentElement;
      let divDisplay = parrentSelect.querySelector('div');
      divDisplay.textContent = child.textContent;
      if (conditions.includes(child.id)) {
        addSearchInputNew(parrentSelect, block, child, listText);
      } else {
        block.remove();
      }
    });
  })
  return neededChildren;
}

function addSearchInputNew(parrent, block, child, listText) {
  let mainParrent = parrent.parentElement;
  mainParrent.after(block);
  let inputSearch = block.querySelector('input');

  inputSearch.id = 'search_' + child.id;
  inputSearch.placeholder = listText[0];
  placeholderInput(inputSearch, child.id, 'designer_dealer', listText[1]);
  inputSearch.focus();
  let cancelBind = createTagHtml('div', 'cancel-bind');
  let acceptBind = createTagHtml('div', 'accept-bind');
  let findCancelBind = block.querySelector('.cancel-bind');
  let findAcceptBind = block.querySelector('.accept-bind');
  if (!findCancelBind && !findAcceptBind) {
    massAppendChild(block, acceptBind, cancelBind);
  }
}


function placeholderInput(input, id, condition, text) {
  if (id === condition) {
    input.placeholder = text;
  }
}


function showForNewUsers(data) {

  if (data) {
    let findInputContractor = document.querySelector('.input-search-bind');
    let findRow = findInputContractor.parentElement;
    let divResult = resultSearchRegUser(data, findStatusBox);
    findRow.appendChild(divResult);
  }
}

function resultSearchRegUser(container, data) {
  let dataArr = Object.values(data);

  let findDivResult = container.querySelector('.result-search-contractors');
  let divResult = findDivResult ? findDivResult : createTagHtml('div', 'result-search-contractors');


}

