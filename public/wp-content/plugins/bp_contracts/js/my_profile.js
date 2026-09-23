/*

*/
"use strict";


function handlerProfil(user) {
  const userParam = user.getAllParam();
  //Find the parrent conteiner box
  let boxMyProfile = document.querySelector('#my-profile');
  boxMyProfile.innerHTML = '';

  //Create all element
  let boxMyName = createTagHtml('div', 'box-for-parametr');
  let titleMyName = createTagHtml('div', 'box-for-title', 'Имя');
  let myName = createTagHtml('div', 'box-for-name', userParam.firstname);

  let boxMyLastname = createTagHtml('div', 'box-for-parametr');
  let titleLastname = createTagHtml('div', 'box-for-title', 'Фамилия');
  let myLastname = createTagHtml('div', 'box-for-lastname', userParam.lastname);

  let boxMyPosition = createTagHtml('div', 'box-for-parametr');
  let titlePosition = createTagHtml('div', 'box-for-title', 'Должность');
  let myPosition = createTagHtml('div', 'box-for-position', userParam.position);

  let boxMyStatus = createTagHtml('div', 'box-for-parametr');
  let titleStatus = createTagHtml('div', 'box-for-title', 'Статус');
  let myStatus = createTagHtml('div', 'box-for-status');

  let boxNotification = createTagHtml('div', 'box-control-email-notification');
  let checboxNotification = createTagHtml("input", "control-email-notification", "", 'email-notification', "checkbox");
  let textNotification = createTagHtml('label', 'text-control-notification', 'Включить E-mail уведомления из Личного Кабинета', 'email-notification');
  checboxNotification.checked = userParam.emailNotification;


  // create our select elements
  let selectStatus;
  if (userParam.statusActivity) {
    selectStatus = createSelectStatusWorker(userParam);
    myStatus.appendChild(selectStatus);
  }
  //Package 
  massAppendChild(
    boxMyProfile,
    boxMyName, boxMyLastname, boxMyPosition, boxMyStatus, boxNotification
  );
  massAppendChild(
    boxMyName,
    titleMyName, myName
  );
  massAppendChild(
    boxMyLastname,
    titleLastname, myLastname
  );
  massAppendChild(
    boxMyPosition,
    titlePosition, myPosition
  );
  massAppendChild(
    boxMyStatus,
    titleStatus, myStatus
  );
  massAppendChild(
    boxNotification,
    checboxNotification, textNotification
  );
  markSelectedOption(userParam);


  checboxNotification.addEventListener('change', function () {
    simpleSendData(checboxNotification.checked, 'setEmailNotification');
  });
}






