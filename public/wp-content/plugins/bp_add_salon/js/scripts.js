async function sendAddSalon(message, subject) {
  try {
    let formData = collectFormRespondAdmin(message, subject);
    let endPoint = 'https://geosideal.ru/wp-content/plugins/bp_add_salon/form/send_add_salon.php';
    let method = 'POST';
    let response = await sendServer(endPoint, method, formData);
    console.log(message);
    console.log(subject);
    console.log(formData);
    if (response.success) {
      console.log(message);
      console.log(subject);
      console.log(formData);
      console.log('Правки внесены');
      alert('Правки внесены');
    } else {
      console.log(message);
      console.log(subject);
      console.log(formData);
      console.warn('Ошибка при внесении правок:', response.message || 'Неизвестная ошибка');
      alert('Ошибка при внесении правок. Пожалуйста, попробуйте еще раз.');
    }
  } catch (error) {
      console.log(message);
      console.log(subject);
      console.log(formData);
    console.error('Ошибка при выполнении запроса:', error);
    alert('Ошибка запроса: не удалось внести правки. Попробуйте позже.');
  }
}

function collectFormRespondAdmin(message, subject) {
  let formData = new FormData();
  formData.append('message', message);
  formData.append('subject', subject);
  return formData;
}

async function sendServer(endPoint, method, formData) {
  let response = await fetch(endPoint, {
    method: method,
    // mode: 'no-cors',
    body: formData,
  });

  if (!response.ok) {
    throw new Error(`Ошибка запроса: ${response.status} ${response.statusText}`);
  }

  return await response.json();
}
