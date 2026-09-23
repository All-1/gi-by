function statusForClient(statusActivity) {
  let statusForClient;
  switch (statusActivity) {
    case 'active':
      statusForClient = 'на работе';
      break;
    case 'noactive':
      statusForClient = 'времено отсутствует';
      break;
    case 'blocked':
      statusForClient = 'больше не работает';
      break;
    case null:
      statusForClient = 'не привязан';
      break;
  }
  return statusForClient;
}

function createSelectStatusWorker(user) {
  let clientStatus = statusForClient(user.statusActivity);
  let selectStatus = createTagHtml('div', 'select-status', clientStatus, 'select-status');
  let arrowForSelect = createTagHtml('div', 'select-status-arrow');
  let boxForOption = createTagHtml('div', 'box-option', '');
  let optionForSelectActive = createTagHtml('div', 'select-option', 'на работе', '', '', ['status', 'active']);
  let optionForSelectNoactive = createTagHtml('div', 'select-option', 'временно отсутствует', '', '', ['status', 'noactive']);
  let optionForSelectBlocked = createTagHtml('div', 'select-option', 'больше не работает', '', '', ['status', 'blocked']);

  massAppendChild(
    selectStatus,
    arrowForSelect, boxForOption
  );
  massAppendChild(
    boxForOption,
    optionForSelectActive, optionForSelectNoactive, optionForSelectBlocked
  );

  // chooseStateForSellect(stateForSelect, selectStatus);
  setEventListenerForSelect(selectStatus, optionForSelectActive, optionForSelectNoactive, optionForSelectBlocked, user);
  return selectStatus;
}

function markSelectedOption(user) {
  if (user.statusActivity) {
    let selectedStatus = document.querySelector('[data-status="' + user.statusActivity + '"]');
    selectedStatus.classList.add('selected-status');
  }
}

function chooseStateForSellect(stateForSelect, selectElement) {
  if (stateForSelect === 'active') {
    selectElement.focus(); // Устанавливаем фокус на элемент
    selectElement.size = selectElement.options.length; // Устанавливаем размер списка равным количеству опций
  }
}

function setEventListenerForSelect(selectMyStatus, optionForSelectActive, optionForSelectNoactive, optionForSelectBlocked, user) {
  let checkBlocked;
  if (user.statusActivity === 'active') {
    setNELForSelectForChange(optionForSelectNoactive, optionForSelectActive, selectMyStatus, user);
    checkBlocked = setNELForSelectBlocked(optionForSelectBlocked, selectMyStatus, user);
  } else if (user.statusActivity === 'noactive') {
    setNELForSelectActive(optionForSelectActive, selectMyStatus, user);
    checkBlocked = setNELForSelectBlocked(optionForSelectBlocked, selectMyStatus, user);
  } else if (!user.statusActivity) {
    setNELForSelectActive(optionForSelectActive, selectMyStatus, user);
    checkBlocked = setNELForSelectBlocked(optionForSelectBlocked, selectMyStatus, user);
  }
  if (checkBlocked || user.statusActivity === 'blocked') {
    optionForSelectActive.remove();
    optionForSelectNoactive.remove();
  }
}
function setNELForSelectBlocked(optionForSelectBlocked, selectMyStatus, user) {
  optionForSelectBlocked.addEventListener('click', async function () {
    let confirmBlocked = await clickChangeStatus(user, 'blocked');
    if (confirmBlocked) {
      setNewStatusUser(selectMyStatus, optionForSelectBlocked, user);
      return true;
    }
  });
}

function setNELForSelectActive(optionForSelectActive, selectMyStatus, user) {
  optionForSelectActive.addEventListener('click', function () {
    let confirmActive = true;
    if (confirmActive) {
      setNewStatusUser(selectMyStatus, optionForSelectActive, user);
    }
  });
}
function setNELForSelectForChange(optionForSelectHtml, optionFSHmlOpposite, selectMyStatus, user) {
  optionForSelectHtml.addEventListener('click', async function () {
    let changesConfirm = await clickChangeStatus(user);
    if (changesConfirm) {
      setNewStatusUser(selectMyStatus, optionForSelectHtml, user);
      setNELForSelect(optionFSHmlOpposite, selectMyStatus, user);
    }
  });
}

function setNELForSelect(optionForSelectHtml, selectMyStatus, user) {
  optionForSelectHtml.addEventListener('click', async function () {
    let changesConfirm = await clickChangeStatus(user);
    if (changesConfirm) {
      setNewStatusUser(selectMyStatus, optionForSelectHtml, user);
    }
  });
}

async function clickChangeStatus(user, statusActivity) {
  return new Promise((resolve) => {
    let shadowBlock = createTagHtml('div', 'shadowBlock');
    let hiddenBlockPotencialReplacement = createTagHtml('div', 'hidden-block-potencial-replacement');
    let closehiddenBlock = createTagHtml('div', 'close-block-potencial-replacement');
    let innerBoxPotencialReplacement = createTagHtml('div', 'inner-box-potencial-replacement');

    let titleInnerBlock = createTagHtml('div', 'title-inner-block', 'Выберите сотрудника для делегирования работы:');
    let blockForReplacement = createTagHtml('div', 'block-for-replacement');
    let blockControlReplacement = createTagHtml('div', 'block-control-replacement');

    let blockForInfo = createTagHtml('div', 'block-for-info');
    let infoText = createTagHtml('p', 'info-text', 'После подтверждения, данный пользователь будет заблокирован, система больше не пустит его в "Кабинет пользователя".');
    let warning = createTagHtml('p', 'warning-blocked', '* !!!РАЗБЛОКИРОВАТЬ ПОЛЬЗОВАТЕЛЯ МОЖЕТ ТОЛЬКО ФАБРИКА');

    let saveChanges = createTagHtml('button', 'save-replacement', 'Сохранить');
    let cancelChanges = createTagHtml('button', 'reduce-changes', 'Отмена');

    document.body.appendChild(hiddenBlockPotencialReplacement);
    massAppendChild(document.body, shadowBlock, hiddenBlockPotencialReplacement);
    massAppendChild(hiddenBlockPotencialReplacement, closehiddenBlock, innerBoxPotencialReplacement);
    massAppendChild(innerBoxPotencialReplacement, titleInnerBlock, blockForReplacement, blockForInfo, blockControlReplacement);
    massAppendChild(blockForInfo, infoText, warning);
    massAppendChild(blockControlReplacement, saveChanges, cancelChanges);

    if (statusActivity !== 'blocked') {
      blockForInfo.remove();
    }

    blockForReplacement = createReplacementBlock(blockForReplacement, user.potencialReplacement);

    saveChanges.addEventListener('click', function () {
      clickSaveChanges(blockForReplacement, user);
      shadowBlock.remove();
      hiddenBlockPotencialReplacement.remove();
      resolve(true);
    });
    cancelChanges.addEventListener('click', function () {
      shadowBlock.remove();
      hiddenBlockPotencialReplacement.remove();
      resolve(false);
    });
    closehiddenBlock.addEventListener('click', function () {
      shadowBlock.remove();
      hiddenBlockPotencialReplacement.remove();
      resolve(false);
    });
  });
}

function createReplacementBlock(blockForReplacement, replacements) {
  replacements.forEach(user => {
    let boxForInput = createTagHtml('div', 'box-choose-replacement');
    let radioInput = createTagHtml('input', 'choose-replacement', '', 'id-replacement-' + user.idUser, 'radio');
    let labelReplacement = createTagHtml('label', 'label-replacement', user.firstname + ' ' + user.lastname);
    radioInput.name = 'replacement';
    radioInput.value = user.idUser;
    labelReplacement.htmlFor = radioInput.id;
    massAppendChild(
      boxForInput,
      radioInput, labelReplacement
    )
    blockForReplacement.appendChild(boxForInput);
  });
  return blockForReplacement;
}

function clickSaveChanges(blockForReplacement, user) {
  const selectedRadio = blockForReplacement.querySelector('input[type="radio"]:checked');
  let replacements = user.potencialReplacement;
  let idReplacement = parseInt(selectedRadio.value);
  user.idUser = parseInt(user.idUser);
  if (checkReplacements(idReplacement, replacements)) {
    let dataReplacement = {
      'userId': user.idUser,
      'hisReplacementId': idReplacement
    }
    let jsonData = JSON.stringify(dataReplacement);
    socket.send('setReplacementForUser::: ' + jsonData);
  }
}

function setNewStatusUser(selectHtmlStatus, optionForSelectHtml, user) {
  let dataStatus = {
    'userId': user.idUser,
    'statusActivity': optionForSelectHtml.dataset.status
  }
  let jsonData = JSON.stringify(dataStatus);
  socket.send('setStatusUser::: ' + jsonData);
}

function checkReplacements(idReplacement, replacements) {
  return replacements.some(user => user.idUser === idReplacement);
}

