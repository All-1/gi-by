function redact_texture(thisid) {
  confirmRedact().then(confirmed => {
    if (confirmed) {
      let thisname = jQuery('#thisname_' + thisid).val();
      let thistype = jQuery('#thistype_' + thisid).val();
      let thisrank = jQuery('#thisrank_' + thisid).val();
      let thisdescr = jQuery('#thisdescr_' + thisid).val();
      let checkedkitchens = [];
      let newfacade;
      let silver;
      let antibac;
      if (jQuery('#new_' + thisid).prop('checked')) {
        newfacade = 'new';
      }
      else {
        newfacade = '';
      }
      if (jQuery('#silver_' + thisid).prop('checked')) {
        silver = 'silver';
      }
      else {
        silver = '';
      }
      if (jQuery('#antibac_' + thisid).prop('checked')) {
        antibac = 'antibac';
      }
      else {
        antibac = '';
      }
      jQuery('.kitchen_checkboxes_' + thisid + ' input:checkbox:checked').each(function () {
        checkedkitchens.push(this.value);
      });
      let kitchensstring = checkedkitchens.toString();
      kitchensstring = kitchensstring.replace(/,/g, ",\" \"");
      let data = { thisid: thisid, thisname: thisname, thistype: thistype, kitchensstring: kitchensstring, thisrank: thisrank, thisdescr: thisdescr, newfacade: newfacade, silver: silver, antibac: antibac };

      jQuery.ajax({
        url: '/wp-content/plugins/bp_facades_list/redact_textures_data.php',
        type: 'POST',
        data: data,
        success: function (data) {
        }
      });
    }
  });
}

function confirmRedact() {
  return new Promise((resolve, reject) => {
    let mainBlock = createTagHtml('div', 'confirmation-block');
    let title = createTagHtml('h2', 'confirmation-title', 'Подтвердите действие');
    let description = createTagHtml('p', 'confirmation-description', 'Вы уверены, что хотите изменить данные текстуры?');
    let buttonConfirm = createTagHtml('button', 'confirmation-button-confirm', 'Подтвердить');
    let buttonCancel = createTagHtml('button', 'confirmation-button-cancel', 'Отмена');
    massAppendChild(mainBlock, title, description, buttonConfirm, buttonCancel);
    document.body.appendChild(mainBlock);

    buttonConfirm.addEventListener('click', function () {
      mainBlock.remove();
      resolve(true);
    });

    buttonCancel.addEventListener('click', function () {
      mainBlock.remove();
      resolve(false);
    });
  });
}

function delete_texture(thisid) {
  let thisfile = jQuery('#file_' + thisid).val();
  confirmRedact().then(confirmed => {
    if (confirmed) {
      jQuery.ajax({
        url: '/wp-content/plugins/bp_facades_list/delete_textures_data.php',
        type: 'POST',
        data: { thisid: thisid, thisfile: thisfile },
        success: function (data) {
          jQuery('#redact_' + thisid).css('display', 'none');
        }
      });
    }
  });
}