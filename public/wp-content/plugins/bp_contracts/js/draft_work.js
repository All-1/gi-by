"use strict";



function sendDataToServer(where, nameCommand, data, show = null) {
  let whereForCase = switchWhere(where);
  let jsonData = data === '' ? JSON.stringify(data) : '';
  socket.send(nameCommand + whereForCase + '::: ' + jsonData);
  if (show) {
    socket.send(nameCommand + whereForCase + '::: ');
  }
}


function setDefaultState_2(where, typeSend, typeInput = null, show = null) {
  let data;
  switch (typeSend) {
    case 'perPage':
      data = getPerPageSelected();
      break;
    case 'currentPage':
      data = getCurrentPage();
      break;
    case 'searchQuery':
      let whereSearch = where === 'contracts' ?
        'search_new_' + where :
        'search_' + where;
      data = takeSearchBar(whereSearch);
      break;
    case 'setFilter':
      let typeFilter = typeInput === 'radio' ? '.tabs-' : '.filters_'; 
      let filter = findElementOnPage(typeFilter, where);
      data = checkCheckedFilterNew(filter, typeInput);
      break;
  }
  sendDataToServer(where, typeSend, data, show);
}

socket.addEventListener('open', function (event) {
  // setDefaultState_2(where, 'perPage');
  // setDefaultState_2(where, 'currentPage');
  // setDefaultState_2(where, 'searchQuery');
  // setDefaultState_2(where, 'setFilter', 'radio');
  // setDefaultState_2(where, 'show', '', true);
  socket.send('show' + 'Contractors' + '::: ');
});

function setDefaultStatePartner(where){
  let whereForCase = switchWhere(where);
  let whereSearch = 'search_' + where;
  let filterContracts = findElementOnPage('.tabs-', where);
  const defaultCurrentPerPage = getPerPageSelected();
  const defaultCurrentPage = getCurrentPage();
  const defaultSearchQuery = takeSearchBar(whereSearch);
  const defaultCheckedNamesNow = checkCheckedFilterNew(filterContracts, 'radio');
  const jsonData = JSON.stringify(defaultCheckedNamesNow);
}