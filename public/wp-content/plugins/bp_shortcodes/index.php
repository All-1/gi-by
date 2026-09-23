<?php
/*Plugin Name: bp_shortcodes
Description: Сопутствующие товары: столы, стулья, комоды.
Version: 1.0
Author: Business Park*/

add_shortcode('cities_window', 'cities_window');
add_shortcode('samples_cities_window', 'samples_cities_window');
add_shortcode('open_full_map', 'open_full_map');
add_shortcode('factoryvsip', 'factoryvsip');
add_shortcode('print_contacts', 'print_contacts');
add_shortcode('dealers_utp_icons', 'dealers_utp_icons');
add_shortcode('dealers_utp_table', 'dealers_utp_table');
add_shortcode('dealers_utp_form', 'dealers_utp_form');
add_shortcode('dealers_utp_form2', 'dealers_utp_form2');
add_shortcode('designer_faq', 'designer_faq');
add_shortcode('designer_utp_icons', 'designer_utp_icons');
add_shortcode('designer_utp_block', 'designer_utp_block');
add_shortcode('designer_register_form', 'designer_register_form');
add_shortcode('contacts_btns', 'contacts_btns');
add_shortcode('contacts_map', 'contacts_map');
add_shortcode('materials_block', 'materials_block');
add_shortcode('modal_window_contracts', 'modal_window_contracts');

function materials_block()
{


	//wp_enqueue_script('chartjs', "https://cdn.jsdelivr.net/npm/chart.js", array(), false, false);
	// $a = "<script src='https://cdn.jsdelivr.net/npm/chart.js'></script>";
	$a = "<script src='/wp-content/plugins/bp_shortcodes/js/chart.js'></script>";


	$a .= "
	<div class='chartclass'>
		<canvas id='myChart'></canvas>
	</div>
	
	<script>
	

	// Any of the following formats may be used
	var ctx = document.getElementById('myChart');
	var ctx = document.getElementById('myChart').getContext('2d');
	var ctx = jQuery('#myChart');

	var ctx = document.getElementById('myChart');
	var ctx = 'myChart';


	var lineChartData = {
        labels : ['ЛДСП','Акрил','МДФ краска','Нат. шпон','FENIX','Массив дерева'],
        datasets : [
			{
                label: 'ИТОГО',
                pointColor : '#deaa93',
				borderColor: '#deaa93',
				backgroundColor: '#deaa93',
                data : [2.18, 2.27, 2.27, 3.27, 3.82, 3.91],
				renderer:function(val){return val},
				borderWidth: 3
            },
            {
                label: 'Устойчивость к повреждениям',
                pointColor : '#D18F32',
				borderColor: '#D18F32',
				backgroundColor: '#D18F32',
                data : [1,1,1,2,5,4],
				hidden: true,
            },
            {
                label: 'Ремонтопригодность',
				pointColor : '#EC614C',
				borderColor: '#EC614C',
				backgroundColor: '#EC614C',
				data : [1,0,0,3,5,4],
				hidden: true,
            },
			{
                label: 'Влагостойкость',
                pointColor : '#588BB2',
				borderColor: '#588BB2',
				backgroundColor: '#588BB2',
				data : [3,4,4,2,5,3],
				hidden: true,
            },
			{
                label: 'Долговечность',
				pointColor : '#03AD8E',
				borderColor: '#03AD8E',
				backgroundColor: '#03AD8E',
				data : [1,1,1,3,5,5],
				hidden: true,
            },
			{
                label: 'Простота в уходе',
                pointColor : '#B0C4C2',
				borderColor: '#B0C4C2',
				backgroundColor: '#B0C4C2',
				data : [5,1,1,3,4,4],
				hidden: true,
            },
			{
                label: 'Неприхотливость',
                pointColor : '#F2D047',
				borderColor: '#F2D047',
				backgroundColor: '#F2D047',
				data : [5,5,5,2,5,2],
				hidden: true,
            },
			{
                label: 'Экологичность',
				pointColor : '#74BEAD',
				borderColor: '#74BEAD',
				backgroundColor: '#74BEAD',
				data : [1,3,2,4,3,5],
				hidden: true,
            },
			{
                label: 'Цветовая палитра',
				pointColor : '#BD2D54',
				borderColor: '#BD2D54',
				backgroundColor: '#BD2D54',
				data : [2,2,5,5,2,5],
				hidden: true,
            },
			{
                label: 'Статусность',
                pointColor : '#083C64',
				borderColor: '#083C64',
				backgroundColor: '#083C64',
				data : [0,3,2,5,4,5],
				hidden: true,
            },
			{
                label: 'Эксклюзивность',
                pointColor : '#6262B6',
				borderColor: '#6262B6',
				backgroundColor: '#6262B6',
				data : [0,1,1,5,3,5],
				hidden: true,
            },
			{
                label: 'Цена',
                pointColor : '#63C1E7',
				borderColor: '#63C1E7',
				backgroundColor: '#63C1E7',
				data : [5,4,3,2,1,1],
				hidden: true,
            }
        ]

    }
	

	var myChart = new Chart(ctx, {

		type: 'line',
		data: lineChartData,
		options: {
			scales: {
				y: {
					beginAtZero: true
				},
			},
			plugins: {
				legend: {
					align: 'start',
					onHover: function() {
						jQuery('#myChart').css('cursor','pointer');
					},
					position: 'bottom',
					labels:{
						padding:16,
						boxWidth: 16,
						boxHeight:16,
						font: {
							size: 16
						},
					},
				},
			},
			maintainAspectRatio: false
			
		}
	});
	
	</script>
	";
	$a .= "
	<div class='materials_material_div_2'>
		<a href='/solid-wood/'>
		<div class='mat3'>
			<div class='material_div_bg matclicking'>
				<div class='material_content matclicking'>
					<p class='standartcontent matclicking'  style='line-height:1.3;'> Массив<br> дерева</p>
					<div class='material_arrow matclicking'>
						<i class='fa fa-chevron-right matclicking'></i>
					</div>
				</div>
			</div>
		</div>
		</a>
		<div class='mat4'>
			<a href='/wood-veneer/'>
			<div class='material_div_bg matclicking'>
				<div class='material_content matclicking'>
					<p class='standartcontent matclicking'> Шпон</p>
					<div class='material_arrow matclicking'>
						<i class='fa fa-chevron-right matclicking'></i>
					</div>
				</div>
			</div>
			</a>
		</div>
	</div>
	<div class='materials_material_div_1'>
		<a href='/fenix-material/'>
		<div class='mat1'>
			<div class='material_div_bg matclicking'>
				<div class='material_content matclicking'>
					<p class='standartcontent matclicking'> FENIX</p>
					<div class='material_arrow matclicking'>
						<i class='fa fa-chevron-right matclicking'></i>
					</div>
				</div>
			</div>
		</div>
		</a>
		<a href='/eterno/'>
		<div class='mat2'>
			<div class='material_div_bg matclicking'>
				<div class='material_content matclicking'>
					<p class='standartcontent matclicking'>Этерно</p>
					<div class='material_arrow matclicking'>
						<i class='fa fa-chevron-right matclicking'></i>
					</div>
				</div>
			</div>
		</div>
		</a>
		<a href='/materials/greenlam/'>
		<div class='mat22'>
			<div class='material_div_bg matclicking'>
				<div class='material_content matclicking'>
					<p class='standartcontent matclicking'>Greenlam</p>
					<div class='material_arrow matclicking'>
						<i class='fa fa-chevron-right matclicking'></i>
					</div>
				</div>
			</div>
		</div>
		</a>
	</div>
	<div class='materials_material_div_2'>
		<a href='/ldsp-material/'>
		<div class='mat5'>
			<div class='material_div_bg matclicking'>
				<div class='material_content matclicking'>
					<p class='standartcontent matclicking'> ЛДСП</p>
					<div class='material_arrow matclicking'>
						<i class='fa fa-chevron-right matclicking'></i>
					</div>
				</div>
			</div>
		</div>
		</a>
		<a href='/mdf-material/'>
		<div class='mat6'>
			<div class='material_div_bg matclicking'>
				<div class='material_content matclicking'>
					<p class='standartcontent matclicking' style='line-height:1.3;'> МДФ<br> краска</p>
					<div class='material_arrow matclicking'>
						<i class='fa fa-chevron-right matclicking'></i>
					</div>
				</div>
			</div>
		</div>
		</a>
	</div>
	
	";

	$a .= "
	<style>
	.chartclass{width:100%; margin-bottom:64px; position:relative;} 
	#myChart{width:100%; max-height:100%; height:400px !important;}
	@media screen and (max-width:600px){
		#myChart{max-height:100%; height:500px !important;}
	}
	
	.materials_material_div_1, .materials_material_div_2, .materials_material_div_3 {display:inline-grid;  grid-gap:32px; width:100%; margin-bottom: 32px;}
	.materials_material_div_1{grid-template-columns:4fr 4fr 4fr;}
	.materials_material_div_2{grid-template-columns:7fr 5fr;}
	.materials_material_div_3{grid-template-columns:5fr 7fr;}
	.material_content{}
	.material_content span {font-size:32px; font-weight:700; font-family:'montserrat'; line-height:1; display:block; max-width:245px; margin-bottom:8px;}
	.material_content .standartcontent {max-width:245px; font-size:22px;}
	.mat1, .mat2, .mat22, .mat3, .mat4, .mat5, .mat6 {background:#f1f2f2; padding:24px; height:300px; position:relative;}
	.mat1{background-image:url(/wp-content/themes/wp-diary/images/fenix-new.jpg); background-repeat: no-repeat; background-position:right top; background-size:contain;}
	.mat2{background-image:url(/wp-content/themes/wp-diary/images/avatar-Eterno-min-new.jpg); background-repeat: no-repeat; background-position:right top; background-size:contain;}
	.mat22{background-image:url(/wp-content/themes/wp-diary/images/greenlam.jpg); background-repeat: no-repeat; background-position:right top; background-size:contain;}
	.mat3{background-image:url(/wp-content/themes/wp-diary/images/massiv.jpg); background-repeat: no-repeat; background-position:right top; background-size:contain;}
	.mat4{background-image:url(/wp-content/themes/wp-diary/images/shpon.jpg); background-repeat: no-repeat; background-position:right top; background-size:contain;}
	.mat5{background-image:url(/wp-content/themes/wp-diary/images/ldsp.jpg); background-repeat: no-repeat; background-position:right top; background-size:contain;}
	.mat6{background-image:url(/wp-content/themes/wp-diary/images/mdf.jpg); background-repeat: no-repeat; background-position:right top; background-size:contain;}
	.material_arrow{position:absolute; bottom:24px; left:24px; width:30px; height:30px;}
	.fa-chevron-right{}
	.material_div_bg{position:absolute; left:0; top:0; width:100%; height:100%; background:transparent; padding:24px;}
	.material_div_bg:hover {background:rgba(222, 169, 147, 0.6); cursor:pointer;}
	.material_div_bg.selected {background:#083c65; color:#fff;}
	.clickedcontent {display:none;}
	.regBtnlink{width:100%; text-align:center; margin-top:64px;}
	
	
	@media screen and (max-width:1000px){
		.materials_firt_div {grid-column-gap:32px;}
		.materials_form_div{grid-column-gap:16px; grid-row-gap:16px;}
		.materials_form{padding:16px;}
		.files_div{width:100%;}
		.materials_material_div_1, .materials_material_div_2, .materials_material_div_3{grid-gap:16px; margin-bottom:16px; grid-template-columns: 1fr 1fr;}
		.mat1, .mat2, .mat3, .mat4, .mat5, .mat6 {background-size:250px; background-position:right bottom;}
		
	}
	@media screen and (max-width:800px){
		.materials_firt_div{display:block;}
		.materials_form{padding:32px;}
		.materials_material_div_1, .materials_material_div_2, .materials_material_div_3{display:block;}
		.mat1, .mat2, .mat3, .mat4, .mat5, .mat6 {background-size:contain;}
	}
	@media screen and (max-width:800px){
		.mat1, .mat2, .mat3, .mat4, .mat5, .mat6 {background-size:250px;}
		.mat5, .mat3, .mat1 {margin-bottom:16px;}
	}
	@media screen and (max-width:550px){
		.mat1, .mat2, .mat3, .mat4, .mat5, .mat6 {background-image:none; height:250px;}
		.materials_form{padding:32px 16px;}
		.materials_form_div{display:block;}
		.materials_form_div div input{margin:8px 0;}
		.des_material_header_h3{margin-top:64px;}
		.materials_form{margin-top:32px;}
	}
	</style>
	
	";
	return $a;
}


function contacts_map()
{

	$a = "<div style='width:100%; min-height:100px; height:400px; box-shadow:3px 6px 18px rgba(1,1,1,0.2);' id='map'></div>";

	$center_lat = 54.043227;
	$center_lng = 28.213834;
	$marker_image = "/wp-content/uploads/2019/04/mymarker2.png";


	$a .= "
		<link rel='stylesheet' href='/wp-includes/leaflet/leaflet.css' />
		<script src='/wp-includes/leaflet/leaflet-src.js'></script>
		<script>
		
		
		
		var tiles = L.tileLayer('https://cartodb-basemaps-{s}.global.ssl.fastly.net/light_all/{z}/{x}/{y}.png', {
			maxZoom: 18,
			attribution: '&copy; <a href=\"https://www.openstreetmap.org/copyright\">OpenStreetMap</a> contributors, Points &copy 2012 LINZ'
		}),
		latlng = L.latLng($center_lat, $center_lng);
		var map = L.map('map', {center: latlng, zoom: 13, layers: [tiles]});

		L.icon = function (options) {
			return new L.Icon(options);
		};

		var myIcon = L.icon({
			iconUrl: '$marker_image',
			iconSize: [30, 39], // size of the icon
		});
		
		L.marker(L.latLng($center_lat, $center_lng), {title: 'Фабрика ГеосИдеал', icon:myIcon, markerId: 1}).addTo(map);



		</script>
		";

	return $a;
}

function contacts_btns()
{
	global $wp_query;
	$post_id = $wp_query->post->ID;
	$url = get_home_url();
	if ($post_id == 4685) {
		$fabr_cont = "selected";
	} else
		$fabr_cont = "unselected";
	if ($post_id == 4712) {
		$distr = "selected";
	} else
		$distr = "unselected";
	if ($post_id == 13217) {
		$salon = "selected";
	} else
		$salon = "unselected";
	$a = "
	<div style=''>
	<a href='/contacts/'><button class='$fabr_cont myBtn'>Контакты фабрики</button></a>
	<a href='/distribyutory/'><button class='$distr myBtn'>Дистрибьюторы</button></a>
	</div>
	<style>
		.selected{background: #1e3350; color:white; margin-right:32px; margin-top:32px;}
		.unselected{background: white; color:#666; margin-right:32px; margin-top:32px;}
	</style>
	";
	return $a;
}

function designer_register_form()
{ ?>
	<script>
		let successMessage = `
		<div style='position:absolute; left:0; right:0; top:0; bottom:0; margin:auto; width:100%; height:max-content; padding:64px;'>
				<div style='font-size:22px; font-weight:700; font-family:\"Montserrat\"; text-align:center; margin-bottom:16px;'>Спасибо за заявку!</div>
				<div style='text-align:center;'>Мы вышлем вам на email доступ в личный кабинет в течение 1 дня. Спасибо за ожидание. Скучать вам не дадим, предлагаем посмотреть <a href='/kuhni/'>каталог кухонь фабрики</a>. Мечтайте, творите, воплощайте…</div>
		</div>`;
		document.addEventListener('DOMContentLoaded', function () {
			let regForm = document.getElementById('regform');
			let buttonSubmit = regForm.querySelector('.regBtn');
			buttonSubmit.addEventListener('click', function (event) {
				let captcha = grecaptcha.getResponse();
				let captchaError = document.getElementById('recaptchaError');
				if (!captcha.length) {
					event.preventDefault();
					console.log('куда ты прёшься');
					captchaError.textContent = '*Пройдите проверку на робота';

				} else {
					regForm.action = '#content';
					sendRegDesigner(regForm);
					var parentElement = regForm.parentNode;
					parentElement.innerHTML = '';
					// Добавить новое содержимое в форму
					parentElement.innerHTML = successMessage;
				}
			});
		});
		function sendRegDesigner(form) {
			let inputName = form.querySelector('input[name=\"username\"]');
			let inputPhone = form.querySelector('input[name=\"phone\"]');
			let inputCity = form.querySelector('input[name=\"city\"]');
			let inputEmail = form.querySelector('input[name=\"mail\"]');
			let endPoint = 'https://geosideal.ru/wp-content/plugins/bp_shortcodes/forms/send_designer_registration.php';
			let method = 'POST';
			let formData = collectFormDataDesigner(inputName, inputPhone, inputCity, inputEmail);
			sendServer(endPoint, method, formData);
		}
		function collectFormDataDesigner(...inputs) {
			let formData = new FormData();
			if (inputs.length > 0) {
				inputs.forEach(input => {
					if (input.value.trim() !== '') {
						formData.append(input.name, input.value);
					}
				});
			}
			return formData;
		}
		function sendServer(endPoint, method, formData) {
			fetch(endPoint, {
				method: method,
				body: formData,
			})
				.then(response => {
					if (!response) {
						throw new Error('Ошибка запроса: ответ от сервера не существует');
					}
					return response.json();
				})
				.then(data => {
					console.log('Ответ сервера:', data);
				})
				.catch(error => {
					console.error('Ошибка при выполнении запроса:', error);
				});
		}
	</script>
	<?php
	$x = "
		<span style='font-size:22px; font-weight:900; font-family:\"Montserrat\"' id='des_reg_form'>Регистрация</span><br>
		<p style='line-height:1.4;'>В личном кабинете сможете просматривать и скачивать 3D модели, текстуры, технический каталог и отслеживать заказ</p>
		<div id='regform'>
			<div class='designers_form_div'>
				<div>
					<input name='username' placeholder='Имя' required/><br>
				</div>
				<div>
					<input name='phone' placeholder='Номер телефона' required/><br>
				</div>
				<div>
					<input name='city' placeholder='Город' required/><br>
				</div>
				<div>
					<input name='mail' type='email' placeholder='E-mail' required/><br>
				</div>

				<div>

					<a href='/login-page/' rel='nofollow' style='font-size:16px; text-decoration:none; margin-top:16px; display:block; color:#003B69; line-height:1.2;'>Уже зарегистрированы?<br> <span style='text-decoration:underline;'>Войти</span></a>
				</div>
				<button class='myBtn regBtn'>Зарегистрироваться</button>
			</div>
			<div>
				<div class='g-recaptcha' data-sitekey='6LcuvLkZAAAAAH-ODI9SDnmSeDvgOdOHAQOfMEND' style='margin: 25px auto 10px; width: 300px;'></div>
				<div class='text-danger' id='recaptchaError' style='color: red; margin: 25px auto 10px; width: 300px;'></div>
			</div>
			<span style='font-size:12px; display:block; margin-top:24px; line-height:1.3;'>Нажимая кнопку \"Зарегистрироваться\", вы даете согласие на обработку персональных данных согласно нашей <a href='/privacy-policy/' target='_blank' rel='nofollow' style='text-decoration:underline;'>политике конфиденциальности</a>
			</span>
		</div>
			
		";


	$a = "
	<div class='designers_firt_div'>
		<div class='hello_text_div'>
			<p>Будем рады сотрудничеству с дизайнерами интерьеров, архитекторами. После регистрации вам будет доступна полная информация:</p>
			<p>Нам важна обратная связь! Вопросы и пожелания присылайте на электронный адрес <a href='mailto:info@gi.by'><span style='color:#DEA993;'>info@gi.by</span></a> или в чат колл-центра.</p>
			<div class='loadfilesdiv'>
				<a href='/kd/files/Catalog 2019.pdf' target='_blank'>
					<div class='files_div' style='border-bottom:1px solid #A5A5A5;'>
					
						<div>
							Каталог кухонь 2019
						</div>
						<div class='loadbtn'>
							<img src='/wp-content/plugins/bp_dealer_files/images/load.png'>
						</div>
					
					</div>
				</a>
				<div class='files_div'>
					<div>
						Технические описания моделей
						<span class='register_message'>Сперва зарегистрируйте кабинет</span>
					</div>
					<div class='loadbtn no_download_file'>
						<img src='/wp-content/plugins/bp_dealer_files/images/load.png'>
					</div>
				</div>
				<div class='files_div'>
					<div>
						3D модели
						<span class='register_message'>Сперва зарегистрируйте кабинет</span>
					</div>
					<div class='loadbtn no_download_file'>
						<img src='/wp-content/plugins/bp_dealer_files/images/load.png'>
					</div>
				</div>
				<div class='files_div'>
					<div>
						Текстуры
						<span class='register_message'>Сперва зарегистрируйте кабинет</span>
					</div>
					<div class='loadbtn no_download_file'>
						<img src='/wp-content/plugins/bp_dealer_files/images/load.png'>
					</div>
				</div>
			</div>
		</div>
		
		<div class='designers_form'>
			<div class='marginautodesignersform'>
				$x
			</div>
		</div>
	</div>
	";


	$a .= "
	<style>
	.designers_firt_div{display:inline-grid; grid-template-columns:5fr 6fr; grid-column-gap:64px; grid-row-gap:32px; width:100%; margin-bottom:32px;}
	.designers_form {width:100%; padding:32px 32px; background:#f1f2f2; position:relative; display:flex; align-items:center;}
	.designers_form_div {width:100%; display:inline-grid; grid-template-columns: repeat(2,1fr); grid-column-gap:32px; grid-row-gap:32px; text-align:left; align-items:center;}
	.designers_form_div div{text-align:left;}
	.designers_form_div div input {width:100%; height:45px; border:1px solid #dcdcdc; font-size:14px; padding:8px 16px;}
	.designers_form_header{font-size:22px; font-weight:600; font-family:'montserrat'; line-height:1.3;}
	.regBtn{margin-top:16px !important;}
	.loadfilesdiv{margin-top:48px;}
	.files_div{width:80%; display:inline-grid; grid-template-columns: 6fr 1fr; border-bottom:1px solid #A5A5A5; text-align:left; padding:0 16px 8px 0; margin-top:16px;}
	.files_div:last-child{border-bottom:none;}
	.files_div div:nth-child(2){text-align:right;}
	.files_div div:nth-child(2) img{width:24px; margin-top:-4px;}
	.loadbtn{cursor:pointer;}
	.register_message{display:none; margin-top:-8px; font-size:12px; color:#e78c68;}
	.marginautodesignersform{}
	
	@keyframes shake {
		0% { transform: translate(1px, 1px) rotate(0deg); }
		10% { transform: translate(-1px, -2px) rotate(-1deg); }
		20% { transform: translate(-3px, 0px) rotate(1deg); }
		30% { transform: translate(3px, 2px) rotate(0deg); background:#dcdcdc;}
		40% { transform: translate(1px, -1px) rotate(1deg); }
		50% { transform: translate(-1px, 2px) rotate(-1deg); }
		60% { transform: translate(-3px, 1px) rotate(0deg); }
		70% { transform: translate(3px, 1px) rotate(-1deg); }
		80% { transform: translate(-1px, -1px) rotate(1deg); }
		90% { transform: translate(1px, 2px) rotate(0deg); }
		100% { transform: translate(1px, -2px) rotate(-1deg); }
	}
	</style>
	
	<script>
	jQuery('.no_download_file').on('click', function(){
		
		var parent = this.parentNode;
		jQuery(parent.childNodes[1].childNodes[1]).css('display','block');
		jQuery('.designers_form').css({'animation':'shake 0.5s', 'animation-iteration-count':'infinite'});
		function stopanimate(){
			jQuery('.designers_form').css({'animation':'none'});
		}
		setTimeout(stopanimate, 500);
		//jQuery('.designers_form').css({'background':'red'});
	});
	</script>
	";


	return $a;
}

function designer_utp_block()
{
	$a = "<h3 class='des_utp_header_h3'>Плюсы сотрудничества с фабрикой ГеосИдеал</h3>";
	$a .= "
	<div class='designers_utp_div_1'>
		<div class='utp1'>
			<div class='utp_div_bg utpclicking'>
				<div class='utp_content utpclicking'>
					<span class='utpdivnum utpclicking'>1</span>
					<p class='standartcontent utpclicking'> Прозрачные условия сотрудничества и ценовая политика</p>
					<p class='clickedcontent utpclicking'> Заключаем договор с вашим ИП на оказание услуг по привлечению клиентов </p>
					<div class='utp_arrow utpclicking'>
						<i class='fa fa-chevron-right utpclicking'></i>
					</div>
				</div>
			</div>
		</div>
		<div class='utp2'>
			<div class='utp_div_bg utpclicking'>
				<div class='utp_content utpclicking'>
					<span class='utpdivnum utpclicking'>2</span>
					<p class='standartcontent utpclicking'>Детальная проработка кухни нашим дизайнером-консультантом, от вас нужен только дизайн-проект</p>
					<p class='clickedcontent utpclicking'> Наши специалисты оптимизируют ваш проект под возможности фабрики</p>
					<div class='utp_arrow utpclicking'>
						<i class='fa fa-chevron-right utpclicking'></i>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class='designers_utp_div_2'>
		<div class='utp3'>
			<div class='utp_div_bg utpclicking'>
				<div class='utp_content utpclicking'>
					<span class='utpdivnum utpclicking'>3</span>
					<p class='standartcontent utpclicking'> Изготавливаем заказ от 35 календарных дней согласно договору</p>
					<p class='clickedcontent utpclicking'> Заказ поступает в работу с момента предоплаты. Возможна 100% предоплата. Нестандартный заказ может незначительно увеличивать срок изготовления</p>
					<div class='utp_arrow utpclicking'>
						<i class='fa fa-chevron-right utpclicking'></i>
					</div>
				</div>
			</div>
		</div>
		<div class='utp4'>
			<div class='utp_div_bg utpclicking'>
				<div class='utp_content utpclicking'>
					<span class='utpdivnum utpclicking'>4</span>
					<p class='standartcontent utpclicking'> Возможность отслеживать, на каком этапе находится ваш заказ</p>
					<p class='clickedcontent utpclicking'> Личный кабинет предоставляет возможность видеть все этапы производства кухни и отслеживать заказ </p>
					<div class='utp_arrow utpclicking'>
						<i class='fa fa-chevron-right utpclicking'></i>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-------<div class='designers_utp_div_3'>
		<div class='utp5'>
			<div class='utp_div_bg utpclicking'>
				<div class='utp_content utpclicking'>
					<span class='utpdivnum utpclicking'>5</span>
					<p class='standartcontent utpclicking'> Личная ответственность менеджера группы сервисного обслуживания фабрики за монтаж</p>
					<p class='clickedcontent utpclicking'> Мы заканчиваем монтаж только при полной удовлетворённости клиента и после подписания акта выполненных работ </p>
					<div class='utp_arrow utpclicking'>
						<i class='fa fa-chevron-right utpclicking'></i>
					</div>
				</div>
			</div>
		</div>
		<div class='utp6'>
			<div class='utp_div_bg utpclicking'>
				<div class='utp_content utpclicking'>
					<span class='utpdivnum utpclicking'>6</span>
					<p class='standartcontent utpclicking'> Профессиональные фотографии объекта для общего портфолио и взаимного пиара в instagram</p>
					<p class='clickedcontent utpclicking'> Договариваемся с клиентом и наш фотограф проводит фотосессию готовой кухни</p>
					<div class='utp_arrow utpclicking'>
						<i class='fa fa-chevron-right utpclicking'></i>
					</div>
				</div>
			</div>
		</div>
	</div>----->
	
	<div class='regBtnlink'>
		<a href='#des_reg_form'>
			<button class='myBtn'>Перейти к регистрации</button>
		</a>
	</div>
	";

	$a .= "
	<style>
	.designers_utp_div_1, .designers_utp_div_2, .designers_utp_div_3 {display:inline-grid;  grid-gap:32px; width:100%; margin-bottom: 32px;}
	.designers_utp_div_1{grid-template-columns:5fr 7fr;}
	.designers_utp_div_2{grid-template-columns:7fr 5fr;}
	.designers_utp_div_3{grid-template-columns:5fr 7fr;}
	.utp_content{}
	.utp_content span {font-size:32px; font-weight:700; font-family:'montserrat'; line-height:1; display:block; max-width:245px; margin-bottom:8px;}
	.utp_content .standartcontent {max-width:245px;}
	.utp1, .utp2, .utp3, .utp4, .utp5, .utp6 {background:#f1f2f2; padding:24px; height:300px; position:relative;}
	.utp1{background-image:url(/wp-content/themes/wp-diary/images/disutp1.png); background-repeat: no-repeat; background-position:right top; background-size:contain;}
	.utp2{background-image:url(/wp-content/themes/wp-diary/images/disutp2.png); background-repeat: no-repeat; background-position:right top; background-size:contain;}
	.utp3{background-image:url(/wp-content/themes/wp-diary/images/disutp3.png); background-repeat: no-repeat; background-position:right top; background-size:contain;}
	.utp4{background-image:url(/wp-content/themes/wp-diary/images/disutp4.png); background-repeat: no-repeat; background-position:right top; background-size:contain;}
	.utp5{background-image:url(/wp-content/themes/wp-diary/images/disutp5.png); background-repeat: no-repeat; background-position:right top; background-size:contain;}
	.utp6{background-image:url(/wp-content/themes/wp-diary/images/disutp6.png); background-repeat: no-repeat; background-position:right top; background-size:contain;}
	.utp_arrow{position:absolute; bottom:24px; left:24px; width:30px; height:30px;}
	.fa-chevron-right{}
	.utp_div_bg{position:absolute; left:0; top:0; width:100%; height:100%; background:transparent; padding:24px;}
	.utp_div_bg:hover {background:rgba(222, 169, 147, 0.6); cursor:pointer;}
	.utp_div_bg.selected {background:#083c65; color:#fff;}
	.clickedcontent {display:none;}
	.regBtnlink{width:100%; text-align:center; margin-top:64px;}
	
	
	@media screen and (max-width:1000px){
		.designers_firt_div {grid-column-gap:32px;}
		.designers_form_div{grid-column-gap:16px; grid-row-gap:16px;}
		.designers_form{padding:16px;}
		.files_div{width:100%;}
		.designers_utp_div_1, .designers_utp_div_2, .designers_utp_div_3{grid-gap:16px; margin-bottom:16px; grid-template-columns: 1fr 1fr;}
		.utp1, .utp2, .utp3, .utp4, .utp5, .utp6 {background-size:250px; background-position:right bottom;}
		
	}
	@media screen and (max-width:800px){
		.designers_firt_div{display:block;}
		.designers_form{padding:32px;}
		.designers_utp_div_1, .designers_utp_div_2, .designers_utp_div_3{display:block;}
		.utp1, .utp2, .utp3, .utp4, .utp5, .utp6 {background-size:contain;}
	}
	@media screen and (max-width:800px){
		.utp1, .utp2, .utp3, .utp4, .utp5, .utp6 {background-size:250px;}
		.utp5, .utp3, .utp1 {margin-bottom:16px;}
	}
	@media screen and (max-width:550px){
		.utp1, .utp2, .utp3, .utp4, .utp5, .utp6 {background-image:none; height:250px;}
		.designers_form{padding:32px 16px;}
		.designers_form_div{display:block;}
		.designers_form_div div input{margin:8px 0;}
		.des_utp_header_h3{margin-top:64px;}
		.designers_form{margin-top:32px;}
	}
	</style>
	
	
	<script>
	jQuery('.utpclicking').on('click', function(){
		jQuery('.utp_div_bg').removeClass('selected');
		jQuery('.utp_div_bg .clickedcontent').css('display','none');
		jQuery('.utp_div_bg .standartcontent').css('display','block');
		jQuery('.utp_div_bg .utp_arrow').css('display','block');
		
		jQuery(this).addClass('selected');
		jQuery('.utp_div_bg.selected .clickedcontent').css('display','block');
		jQuery('.utp_div_bg.selected .standartcontent').css('display','none');
		jQuery('.utp_div_bg.selected .utp_arrow').css('display','none');
	});
	jQuery('body').on('click', function(event){
		var clickeddiv = event.target;
		if(!clickeddiv.classList.contains('utpclicking')){
			jQuery('.utp_div_bg').removeClass('selected');
			jQuery('.utp_div_bg .clickedcontent').css('display','none');
			jQuery('.utp_div_bg .standartcontent').css('display','block');
			jQuery('.utp_div_bg .utp_arrow').css('display','block');
		}
		
	});
	</script>
	";
	return $a;
}

function designer_utp_icons()
{

	global $lang_adm;
	if ($lang_adm == NULL) {
		include "lang-admin.php";
		$thispage = "not_designers_cabinet";
	}
	$loadfileslink = "/designers-architects/";
	if (is_user_logged_in()) {
		$current_user_id = get_current_user_id();
		$user = get_userdata($current_user_id);
		$user_roles = $user->roles;
		// Check if the role you're interested in, is present in the array.
		if (in_array('designer_architect', $user_roles, true)) {
			// Do something.
			$user_role = "designer_architect";
			$loadfileslink = "/load-files/";
		} else {
			$user_role = "dealer";

		}

	}

	//echo "<script>console.log('$loadfileslink');</script>";
	if ($thispage !== "not_designers_cabinet") {
		$a = "
		<h1>$lang_adm->di_h1</h1>
		<p>$lang_adm->di_p_1</p>
		<p>$lang_adm->di_p_2</p>
		";
	} else
		$a = "";

	$a .= "
	
	<div class='designers_utp_icons'>
		<a href='/kd/files/ГЕНЕРАЛЬНЫЙ_КАТАЛОГ_2024.pdf' target='_blank'>
		<div class='some_dis_utp_div'>
			<div class='some_dis_utp_imagediv'>
				<img src='/wp-content/themes/wp-diary/images/cat2.png'/>
			</div>
			<div class='some_dis_utp_textdiv'>
				$lang_adm->di_catalogue
			</div>
		</div>
		</a>
		<a href='$loadfileslink'>
		<div class='some_dis_utp_div'>
			<div class='some_dis_utp_imagediv'>
				<img src='/wp-content/themes/wp-diary/images/tech2.png'/>
			</div>
			<div class='some_dis_utp_textdiv'>
				$lang_adm->di_techn_descr
			</div>
		</div>
		</a>
		<a href='$loadfileslink'>
		<div class='some_dis_utp_div'>
			<div class='some_dis_utp_imagediv'>
				<img src='/wp-content/themes/wp-diary/images/models2.png'/>
			</div>
			<div class='some_dis_utp_textdiv'>
				$lang_adm->di_3d
			</div>
		</div>
		</a>
		<a href='$loadfileslink'>
		<div class='some_dis_utp_div'>
			<div class='some_dis_utp_imagediv'>
				<img src='/wp-content/themes/wp-diary/images/texture2.png'/>
			</div>
			<div class='some_dis_utp_textdiv'>
				$lang_adm->di_textures
			</div>
		</div>
		</a>
	</div>
	
	";

	$a .= "
	<style>
	.designers_utp_icons{width:100%; margin:16px 0 32px 0; display:inline-grid; grid-template-columns:repeat(4, 1fr); grid-gap:16px;}
	.some_dis_utp_div{text-align:right; width:100%; display:inline-grid; grid-template-columns:1fr 2fr; grid-gap:16px; padding:32px 16px; border:1px solid #f9f9f9; background:#f7f7f7; /*box-shadow:2px 2px 12px rgba(1,1,1,0.1);*/ align-items: center;}
	.some_dis_utp_imagediv img{max-height:47px;}
	.some_dis_utp_textdiv {line-height:1.3; text-align:left;}
	@media screen and (max-width:800px){
		.designers_utp_icons{grid-template-columns:repeat(2,1fr);}
	}
	@media screen and (max-width:500px){
		.designers_utp_icons{grid-template-columns:repeat(1,1fr);}
	}
	</style>
	";
	return $a;
}


function designer_faq()
{

	$a = "<div style='margin-top:48px;'>";

	global $wpdb;
	global $lang_adm;

	$sql = "SELECT * FROM gi_designer_faq ORDER BY rank";
	$result = $wpdb->get_results($sql);
	$i = 0;

	if ($result) {
		$a .= "<h2>FAQ</h2>";
	}
	foreach ($result as $row) {
		$i++;
		$q = $row->question;
		$ans = $row->answer;
		$newid = $row->newid;
		if ($i == 1) {
			$selected = 'selected';
		} else
			$selected = "";

		$a .= "
		<div class='faq_div'>
			<div id='question_$newid' class='question_div $selected' data-id='$newid' onclick='showquestion(this);'>
				<div class='q_text'>$q</div>
				<div class='q_image'>
					<img src='/wp-content/plugins/bp_dealer_files/images/arrow-down.png' style='height:25px;'/>
				</div>
				<div style='clear:both'></div>
			</div>
			<div id='answer_$newid' class='answer_div $selected'>
				<p>
					$ans
				</p>
			</div>
		</div>";
	}
	$a .= "</div>
	<div style='margin-top:64px;'>
		<div style='display:inline-block; margin-right:32px;     vertical-align: top;'>
			$lang_adm->di_have_questions:
		</div>
		<div class='morequestions'>
			<a href='mailto:info@gi.by'>info@gi.by </a><br>
			<a href='tel:+375293741081'>+375(29) 374-10-81 </a> <br>
			<a href='tel:+375336761836'>+375(33) 676-18-36 </a>
		</div>
	</div>
	";
	$a .= "
	<style>
	.faq_div{padding:16px; margin:24px 0; box-shadow:3px 8px 16px rgba(1,1,1,0.0); background:#f8f8f8;}
	.answer_div {display:none;}
	.answer_div.selected {display:block; background:#f8f8f8 !important; margin-top:16px;}
	.answer_div p {max-width:800px;}
	.question_div{background:#f8f8f8; cursor:pointer; font-weight:400; font-size:20px;}
	.question_div.selected{background:#f8f8f8 !important; border-bottom:1px solid #e8e8e8;}
	.question_div.selected .q_text{color:#DEA993 !important; margin-bottom:16px;}
	.question_div.selected .q_image {transform:rotate(180deg);}
	.q_text{float:left; max-width:calc(100% - 50px); line-height:1.4;}
	.q_image{float:right; margin-top:-8px;}
	
	.morequestions {vertical-align: top; display:inline-block;}
	.morequestions a {color:#e78c68; text-decoration:underline;}
	
	@media screen and (max-width:600px){
		.faq_div{padding:32px 16px;}
	}
	</style>
	";

	$a .= "
	<script>
	function showquestion(obj){
		var thisid = jQuery(obj).data('id');
		if(obj.classList.contains('selected')){
			jQuery('.answer_div').removeClass('selected');
			jQuery('.question_div').removeClass('selected');
		}
		else{
			jQuery('.answer_div').removeClass('selected');
			jQuery('.question_div').removeClass('selected');
			jQuery('#answer_'+thisid).addClass('selected');
			jQuery('#question_'+thisid).addClass('selected');
			console.log(thisid);
		}
		
	}
	</script>
	";

	return $a;
}

function dealers_utp_form2()
{
	$phpself = $_SERVER['PHP_SELF'];
	$a = "
	<div class='dealersformdiv'>
		<div class='dealersform'>
			<form method='POST' enctype='multipart/form-data' action='/usloviya-sotrudnichestva/' >
				<input name='city' placeholder='Ваш город' required></input>
				<input name='name' placeholder='Ваше имя' required></input>
				<input name='phone' placeholder='Ваше телефон' required></input>
				<input name='mail' placeholder='Почтовый ящик' required></input>
				<textarea name='message' placeholder='Введите сообщение' required></textarea>
				<br><br>
				<input name='robots' style='display:none;'/>
				<button type='submit' class='myBtn'>Отправить</button>
				<span style='font-size:12px; display:block; margin-top:24px; line-height:1.3;'>Нажимая кнопку \"Отправить\" вы даете согласие на обработку персональных данных согласно нашей <a href='/privacy-policy/' target='_blank' rel='nofollow' style='text-decoration:underline;'>политике конфиденциальности</a></span>
			</form>
		</div>
		
		<div class='dealercontacts'>
			<span class='dealercontactsheader'>Контакты фабрики</span>
			
			<span class='dealercontactsheader'>Отдел продаж:</span> <br><br>
			<span class='dealercontactstext'>
			Начальник отдела продаж - Денис Михайленко
			<br> <a href='tel:+375177638169'>+375 1776 38 169</a> <br> 
			<a href='mailto:info@gi.by'>info@gi.by</a></span><br><br>
			
			<span class='dealercontactsheader'>Отдел продаж, регион Россия, Казахстан:</span><br><br>
			Менеджер по экспорту - Анфимов Александр		
			<span class='dealercontactstext'><br> <a href='tel:+375296235115'>+375 29 623 51 15</a> <br> 
			<a href='mailto:info@gi.by'>alexandr.anfimov@gi.by</a></span><br><br>
			
			<span class='dealercontactsheader'>Отдел продаж, регион Украина:</span><br><br>
			Дистрибьютор в Украине Виталий Морозов 		
			<span class='dealercontactstext'><br> <a href='tel:+380688652886'>+38 068 865 28 86</a> <br>
			<a href='mailto:morozov.geosideal@gmail.com'>morozov.geosideal@gmail.com</a></span> <br><br>
			
			<a href='/contacts/distribyutory/'><button class='myBtn'>Смотреть контакты дистрибьюторов</button></a>
		</div>
	</div>
	";

	return $a;

	if (!empty($_POST['city']) and !empty($_POST['name']) and !empty($_POST['phone']) and !empty($_POST['mail']) and !empty($_POST['message']) and empty($_POST['robots'])) {
		$city = $_POST['city'];
		$name = $_POST['name'];
		$phone = $_POST['phone'];
		$mail = $_POST['mail'];
		$message = $_POST['message'];
		$content = "
			Сообщение с сайта gi.by<br>
			Город: $city <br>
			Имя: $name <br>
			Телефон: $phone <br>
			Почта: $mail <br>
			Сообщение: $message 
			";
		$subject = "Запрос с сайта gi.by";
		$headers = "Content-type: text/html; charset=utf-8\r\n";
		mail('info@gi.by', $subject, $content, $headers);
		//@mail('geosideal@gmail.com', $subject, $content, $headers);
	} else {
		echo "Вы - робот, я вот чувствую...";
	}

}
function dealers_utp_form()
{
	$phpself = $_SERVER['PHP_SELF'];
	$a = "
	<div class='dealersformdiv'>
		<div class='dealersform'>
			Остались вопросы? Напишите нам!
			<form method='POST' enctype='multipart/form-data' action='/usloviya-sotrudnichestva/' >
				<input name='city' placeholder='Ваш город' required></input>
				<input name='name' placeholder='Ваше имя' required></input>
				<input name='phone' placeholder='Ваше телефон' required></input>
				<input name='mail' placeholder='Почтовый ящик' required></input>
				<textarea name='message' placeholder='Введите сообщение' required></textarea>
				<br><br>
				<input name='robots' style='display:none;'/>
				<button type='submit' class='myBtn'>Отправить</button>
				<span style='font-size:12px; display:block; margin-top:24px; line-height:1.3;'>Нажимая кнопку \"Отправить\" вы даете согласие на обработку персональных данных согласно нашей <a href='/privacy-policy/' target='_blank' rel='nofollow' style='text-decoration:underline;'>политике конфиденциальности</a></span>
			</form>
		</div>
		
		<div class='dealercontacts'>
			<span class='dealercontactsheader'>Контакты фабрики</span>
			
			<span class='dealercontactsheader'>Отдел продаж, регион Беларусь:</span> <br><br>
			<span class='dealercontactstext'>
			Начальник отдела продаж, Беларусь - Денис Михайленко
			<br> <a href='tel:+375175081437'>+375 17 508 14 37</a> <br> 
			<a href='mailto:info@gi.by'>info@gi.by</a></span><br><br>
			
			<span class='dealercontactsheader'>Отдел продаж, регион Россия, Казахстан:</span><br><br>
			Менеджер по экспорту, Российская Федерация - Анфимов Александр		
			<span class='dealercontactstext'><br> <a href='tel:+375296235115'>+375 29 623 51 15</a> <br> 
			<a href='mailto:info@gi.by'>alexandr.anfimov@gi.by</a></span><br><br>
			
			<span class='dealercontactsheader'>Отдел продаж, регион Украина:</span><br><br>
			Дистрибьютор в Украине Виталий Морозов 		
			<span class='dealercontactstext'><br> <a href='tel:+380688652886'>+38 068 865 28 86</a> <br>
			<a href='mailto:morozov.geosideal@gmail.com'>morozov.geosideal@gmail.com</a></span> <br><br>
			
			<a href='/contacts/distribyutory/'><button class='myBtn'>Смотреть контакты дистрибьюторов</button></a>
		</div>
	</div>
	";

	return $a;

	if (!empty($_POST['city']) and !empty($_POST['name']) and !empty($_POST['phone']) and !empty($_POST['mail']) and !empty($_POST['message']) and empty($_POST['robots'])) {
		$city = $_POST['city'];
		$name = $_POST['name'];
		$phone = $_POST['phone'];
		$mail = $_POST['mail'];
		$message = $_POST['message'];
		$content = "
			Сообщение с сайта gi.by<br>
			Город: $city <br>
			Имя: $name <br>
			Телефон: $phone <br>
			Почта: $mail <br>
			Сообщение: $message 
			";
		$subject = "Запрос с сайта gi.by";
		$headers = "Content-type: text/html; charset=utf-8\r\n";
		mail('info@gi.by', $subject, $content, $headers);
		//@mail('geosideal@gmail.com', $subject, $content, $headers);
	} else {
		echo "Вы - робот, я вот чувствую...";
	}

}
function dealers_utp_table()
{

	$a = "
	<div class='dealerutpdiv'>
		<div class='dealerutpheader'>
			Монобрендовый салон
		</div>
		<div class='dealerutpcontent'>
			<ul>
				<li>Дополнительная скидка для успешного старта</li>
				<li>Экспозитор в подарок</li>
				<li>Совместная работа над салоном с дизайнером фабрики</li>
				<li>Пониженные коэффиценты</li>
				<li>Запуск салона за 1.5 месяца</li>
				<li>Готовый пакет рекламных материалов, кампаний и сайт</li>
				<li>Дополнительная рекламная и финансковая поддержка</li>
			</ul>
		</div>
	</div>

	<div class='dealerutpdiv'>
		<div class='dealerutpheader'>
			Дилерский салон
		</div>
		<div class='dealerutpcontent'>
			<ul>
				<li>Работа с несколькоими фабриками и широким ассортиментом</li>
				<li>Минимум затрат на образцы</li>
				<li>Отрисовка образцов дизайнером фабрики</li>
				<li>Коэффицент от объема продаж</li>
				<li>Дополнительные скидки для успешного старта</li>
				<li>Отличная альтернатива дорогим кухням</li>
			</ul>
		</div>
	</div><br><br>
	";
	return $a;
}

function dealers_utp_icons()
{
	$a = "
	<div class='dealersutpicons'>
		<div class='dealerutpicondiv'>
			<div class='dealerutpimage'>
				<img src='https://ideal-kuhni.ru/wp-content/themes/wp-diary/images/kachestvo.png'/>
			</div>
			Европейское качество по белорусской цене
		</div>
		<div class='dealerutpicondiv'>
			<div class='dealerutpimage'>
				<img src='https://ideal-kuhni.ru/wp-content/themes/wp-diary/images/models.png'/>
			</div>
			21 модель кухонь и 9 типов материалов
		</div>
		<div class='dealerutpicondiv'>
			<div class='dealerutpimage'>
				<img src='https://ideal-kuhni.ru/wp-content/themes/wp-diary/images/pc.png'/>
			</div>
			Предоставление <b style='color:#13874d;'>ПО</b> для работы
		</div>
		<div class='dealerutpicondiv'>
			<div class='dealerutpimage'>
				<img src='https://ideal-kuhni.ru/wp-content/themes/wp-diary/images/study.png'/>
			</div>
			Обучение сотрудников и проведение вебинаров
		</div>
	</div>
	";
	return $a;
}

function print_contacts($atts)
{
	//параметр к шорткоду (фабрика, дистрибьютор)
	extract(shortcode_atts(array(
		'type' => ''
	), $atts));
	global $wpdb;
	$pageid = get_the_ID();
	if ($type == 'factory') {
		$zoom = 9;
	} elseif ($type == 'distibutor') {
		$zoom = 5;
	} else
		$zoom = 4;

	//КОНТАКТЫ ФАБРИКИ!!!!
	if ($type == 'factory') {

		$a .= "<div style='width:100%; position:relative; margin:64px 0; line-height:1.5;'>";
		$sql_call_office = "SELECT * FROM gi_contacts WHERE subsection = 'Сall-center'";
		$result_call_office = $wpdb->get_results($sql_call_office);
		foreach ($result_call_office as $row_call_office) {
			$subsection = $row_call_office->subsection;
			$contact_post = $row_call_office->contact_post;
			$employee = $row_call_office->employee;
			$phones = $row_call_office->phones;
			$mailbox = $row_call_office->mailbox;
			$grafic = "пн. - пт.: 9.00 - 20.00 <br>сб., вс.: 9.00 - 18.00";

		}
		$a .= "
			<div class='onecontact_div'>
				<p class='h1' style='line-height:1.2;'>$subsection</p>
				$contact_post - <br><b>$employee</b><br><br>
				$phones<br>
				<a class='ga_ym_t' href='tel:+74993467085'>+7(499) 346-70-85</a><br><br>
				$grafic<br><br>
				$sales
			</div>
		";
		$a .= "
			<div class='onecontact_div'>
				<p class='h1'  style='line-height:1.2;'>Приемная</p>
				Секретарь - <b>Екатерина Левкович</b><br>
				<a class=\"ga_ym_t\" href=\"tel:375175555226\">+37517 555 52 26</a> <br><br>
				<b>Время работы:</b> <br>
				пн. - пт.: 8.30 - 16.30 <br>
				сб, вс. - выходные
			</div>
		";
		$a .= "<hr>
		</div>";

		$sql_prod = "SELECT * FROM gi_contacts WHERE contact_post LIKE '%Менеджер по экспорту%' OR contact_post LIKE '%Начальник отдела продаж%'";
		/*$result_prod = $wpdb->get_results($sql_prod);
			foreach($result_prod as $row_prod){
				
			}*/
		$a .= "<div style='width:100%; position:relative; margin:64px 0; line-height:1.5;'>";
		$a .= "
			<div class='onecontact_div'>
				<p class='h1' style='line-height:1.2;'>Отдел продаж</p>
				Начальник отдела продаж<br><b>Валерия Дубовик</b><br>
				<a class=\"ga_ym_t\" href=\"tel:375298600416\">+375 29 860 04 16</a><br>
				valeria.dubovik@gi.by<br><br>
				
				Менеджер по развитию, регион Беларусь<br> <b>Ксения Губич</b><br>
				<a class=\"ga_ym_t\" href=\"tel:375445250723\">+375 44 525 07 23</a><br>
				ksenia.gubich@gi.by<br><br>
				
				Менеджер по экспорту, регион Россия<br> <b>Ксения Губич</b><br>
				<a class=\"ga_ym_t\" href=\"tel:375445250723\">+375 44 525 07 23</a><br>
				ksenia.gubich@gi.by<br><br>

			</div>
		";
		$a .= "
			<div class='onecontact_div'>
				<p class='h1'  style='line-height:1.2;'>Отдел маркетинга</p>
				Начальник отдела маркетинга и рекламы  <br><b>Наталья Автухова</b> <br>
				<a class=\"ga_ym_t\" href=\"tel:375177638169\">+375 1776 38 169</a><br>
				<a class=\"ga_ym_t\" href=\"tel:375445576194\">+375 44 557 61 94</a><br>
				info@gi.by<br><br><br>

				<p class='h1'  style='line-height:1.2;'>Отдел снабжения</p>
				Начальник отдела снабжения  <br><b>Лилия Добищук</b><br>
				<a class=\"ga_ym_t\" href=\"tel:375296863015\">+375 29 686 30 15</a><br>
				supply.geosideal@gmail.com</b>
			</div>
		";
		$a .= "
			<div class='onecontact_div'>
				<p class='h1'  style='line-height:1.2;'>Отдел персонала</p>
				Менеджер по персоналу  <br><b>Наталья Лазук</b><br>
				<a class=\"ga_ym_t\" href=\"tel:375177638165\">+375 17 763 81 65</a><br>
				<a class=\"ga_ym_t\" href=\"tel:375447080825\">+375 44 708 08 25</a><br>
				<a class=\"ga_ym_t\" href=\"tel:375292559099\">+375 29 255 90 99</a><br>
				personal@gi.by<br><br>

				<p class='h1'  style='line-height:1.2;'>Бухгалтерия</p>
				Главный бухгалтер  <br><b>Елена Николаева</b><br>
				<a class=\"ga_ym_t\" href=\"tel:375175555227\">+375 17 555 52 27</a><br>
				<a class=\"ga_ym_t\" href=\"tel:375296539049\">+375 29 653 90 49</a><br>
				account.geosideal@gmail.com<br>

			</div>
		";
		$a .= "<br><br><br><hr></div>";


		$a .= "
		<div style='width:100%; position:relative; margin:64px 0 32px 0; line-height:1.5;'>
			<div class='onecontact_div'>
				<p class='h1'  style='line-height:1.2;'>Склад готовой продукции</p>
				Начальник склада - <b>Александр Кутовский</b><br>
				<a class=\"ga_ym_t\" href=\"tel:375445763648\">+375 44 576 36 48</a> <br>
				GPS: 54.043392, 28.212693<br>
				пн. - пт.: 9.00 - 16.00<br>
				supply.geosideal@gmail.com<br><br><br>
			</div>
			<hr>
		</div>
		";

		$a .= "
		<div style='width:100%; position:relative; margin:64px 0 32px 0; line-height:1.5;'>
			<div class='onecontact_div'>
				<p class='h3'  style='line-height:1.2; margin-top:32px;'>Юридический адрес</p>
				<span style=''>ООО ГеосИдеал<br> Генеральный директор -<br> <b>Владимир Иванович Михайленко</b><br>
				Минская область, Смолевичский район, Плисский с/с, вблизи п. Октябрьского, д. 11, каб. 51<br> 222220<br> 
				УНП 101243849<br>ОКПО 37434319<br>
Р/счет BY24PJCB30120170171000000933<br>
Банк ЦБУ 109<br>
ОАО «Приорбанк»<br>
Адрес банка г. Минск, ул. Притыцкого, 91<br>
BIC PJCBBY2X<br>

				<a class=\"ga_ym_t\" href=\"tel:375175555226\">+37517 555 52 26</a><br> info@gi.by</span>				
				</div>
			<div class='onecontact_div'>
				<p class='h3' style='line-height:1.2; margin-top:32px;'>Почтовый адрес</p>
				<span style=''>222220, Минская область, Смолевичский район, п/о Плиса, а/я 34 <br></span>
				<a class=\"ga_ym_t\" href=\"tel:375175555226\">+375 17 555 52 26</a>
			</div>
		</div>
		";

		$a .= "
		</div>";
		$a .= "<div style='width:100%; min-height:100px; height:400px; box-shadow:3px 6px 18px rgba(1,1,1,0.2);' id='map'></div>";

		$center_lat = 54.043227;
		$center_lng = 28.213834;
		$marker_image = "/wp-content/uploads/2019/04/mymarker2.png";


		$a .= "
		<link rel='stylesheet' href='/wp-includes/leaflet/leaflet.css' />
		<script src='/wp-includes/leaflet/leaflet-src.js'></script>
		<script>
		
		
		
		var tiles = L.tileLayer('https://cartodb-basemaps-{s}.global.ssl.fastly.net/light_all/{z}/{x}/{y}.png', {
			maxZoom: 18,
			attribution: '&copy; <a href=\"https://www.openstreetmap.org/copyright\">OpenStreetMap</a> contributors, Points &copy 2012 LINZ'
		}),
		latlng = L.latLng($center_lat, $center_lng);
		var map = L.map('map', {center: latlng, zoom: 13, layers: [tiles]});

		L.icon = function (options) {
			return new L.Icon(options);
		};

		var myIcon = L.icon({
			iconUrl: '$marker_image',
			iconSize: [30, 39], // size of the icon
		});
		
		L.marker(L.latLng($center_lat, $center_lng), {title: 'Фабрика ГеосИдеал', icon:myIcon, markerId: 1}).addTo(map);



		</script>
		
		<style>
		.onecontact_div{width:calc(33.33% - 37px); display:inline-block; min-height:100px; margin-right:32px; vertical-align:top;}
		.onecontact_div h2 {margin-top:32px;}
		@media screen and (max-width:1024px){
			.onecontact_div {width:calc(50% - 37px);}
		}
		@media screen and (max-width:600px){
			.onecontact_div {width:calc(100%); margin-right:0;}
		}
		</style>
		";
		return $a;
	} elseif ($type == 'distibutor') {
		$b = "
		<div class='onecontact_div' style='margin-top:0; '>
			<p class='h3' style='margin-top:0 !important;'>Москва</p>
			<b>ООО \"АВАНТА\"</b><br>
			125212, г. Москва, Кронштадский бульвар, 14<br><br><br>

			<b>Директор</b> - Елена Агеева<br>
			<a href='tel:+79776000070'>+7 977 600 00 70</a> <br>
			belmos@yandex.ru <br>
		</div>
		<div class='onecontact_div' style='margin-top:0 ;'>
			<p class='h3' style='margin-top:0 !important;'>Москва и область</p>
			<b>ООО \"БАЙМЕБЕЛЬ\"</b><br>
			МО, Одинцовский район, г. Кубинка, ул. Железнодорожная, д. 1А, стр. 1, пом. 21<br><br>

			<b>Ген. директор</b> - Игорь Бендаржевский <br>
			<a href='tel:+79779190292'>+7 977 919 02 92</a> <br>
			bendarzhevski.gi@gmail.com <br>
		</div>
		<div class='onecontact_div' style='margin-top:0;'>
			<p class='h3' style='margin-top:0 !important;'>Санкт-Петербург</p>
			<b>ООО \"КРОМ\"</b><br>
			195248, Санкт-Петербург, ул. Партизанская, 25<br><br><br>
			<b>Директор</b> - Александр Сазонов<br>
			<a href='tel:+7 921 914 03 12'>+7 921 914 03 12</a> <br>
			alexsaz0312@yandex.ru <br>
		</div>
		
		<br><br><br><hr>

		<div class='onecontact_div' style='margin-top:0;'>
			<p class='h3'>Центр и юг европейской части РФ (без Москвы)</p>
			<b>ООО \"Шеф-кухни\"</b><br>
			
			394030, г. Воронеж, ул. Плехановская, д. 50, пом. I в Лит. А<br><br>
			
			<a href='tel:+7 920 212 41 40'>+7 920 212 41 40</a> <br>
			2101726@tut.by <br>
		</div>
		<div class='onecontact_div' style='margin-top:64px;'>
			<p class='h3' style='margin-top:0 !important;'>Север и северо-восток РФ</p>
			<b>ООО \"ИдеалДрев\"</b><br>
			197374, Санкт-Петербург, ул.Школьная., д. 108, лит. А<br><br>
			<b>Директор</b> - Андрей Драгун<br>
			<a href='tel:+7 911 929 88 20'>+7 911 929 88 20</a> <br>
			fabrika.geosideal@mail.ru <br>
		</div>
		<br><br><br><hr>
		<div style='width:100%; display:block; min-height:100px; margin-right:32px; margin-top:0; vertical-align:top;'>
			<p class='h3'>Украина</p>
			<b>ООО \"Сэлвин-Стайл\"</b><br>
			г. Киев, ул. Краснозаводская, д. 7, оф. 39<br><br>
			<b>Генеральный директор</b> - Виталий Морозов<br>
			<a href='tel:+38 068 865 28 86'>+38 068 865 28 86</a> <br>
			morozov.geosideal@gmail.com <br>
		</div>
		";
		$b .= "
		<style>
		.onecontact_div{width:calc(33.33% - 37px); display:inline-block; min-height:100px; margin-right:32px; vertical-align:top;}
		.onecontact_div h2 {margin-top:32px;}
		@media screen and (max-width:1024px){
			.onecontact_div {width:calc(50% - 37px);}
		}
		@media screen and (max-width:600px){
			.onecontact_div {width:calc(100%); margin-right:0;}
		}
		</style>
		";
		return $b;
	}
}

function factoryvsip()
{

	$a = "

<style>
.samopil_header{padding:10px 20px; text-transform:uppercase; cursor:pointer; border:1px solid #dcdcdc; background:#fafafa; font-weight:600; position:relative; left:0; right:0; margin:20px auto 50px auto; width:100%; max-width:100%; text-align:center;}
.samopil_content{width:100%; transition:0.5s ease; text-align:left; background:#fafafa; border:1px solid #dcdcdc; padding:10px; margin:10px auto 20px auto; font-size:18px;}
.samopil_image_left{float:left; width:400px; max-width:100%; margin-top:-40px; margin-right:20px;}
.samopil_text{width:calc(100% - 420px); float:right; padding:0 10px;}
.samopil_arrows{width:100%;}
.samopil_left_arrow, .samopil_right_arrow{width:50%; float:left; text-align:center; margin:20px 0;}
.samopil_left_arrow img{width:60px;}
.samopil_right_arrow img{width:60px;}
.double_samopil_content{}
.left_samopil_content{width:calc(50% - 10px); margin-left:10px; min-width:calc(50% - 20px); float:right;}
.right_samopil_content{width: calc(50% - 10px); margin-right:10px; min-width:calc(50% - 10px); float:right;}
.left_samopil_content .samopil_content img {float:none; width:100%;}
.right_samopil_content .samopil_content img {float:none; width:100%;}
.left_samopil_content .samopil_content .samopil_text, .right_samopil_content .samopil_content .samopil_text {float:none; width:100%; margin-top:20px;}
.samopil_header_cursor_image{width:40px; height:40px; background:url(https://avatanplus.com/files/resources/mid/56ae0d355206d15297e39858.png) no-repeat; background-position:center; background-size:contain; position:absolute; bottom:-20px; right:-20px; transform:rotate(-25deg); display:none;}

.samopil {border: 2px solid #595959; color:white; background:#595959; font-weight:200; margin:20px auto 10px auto;}
.factory {border: 2px solid #38b050; color:white; background:#38b050; font-weight:200; margin:20px auto 10px auto;}

/*.right_samopil_content .samopil_text ul li {list-style:inside; list-style-image:url(http://picture-cdn.wheretoget.it/dhb0iq-i.jpg); }
.left_samopil_content .samopil_text ul li {list-style:inside; list-style-image:url(https://www.epson.es/images/inkcolors/k.png); }*/

@media screen and (max-width:1100px){
	.samopil_image_left {width:300px;}
	.samopil_text{width:calc(100% - 320px);}
}
@media screen and (max-width:900px){
	.left_samopil_content, .right_samopil_content{ width:100%; margin:0;}
	.samopil_content {max-width:100%; width:100%; padding:0;}
	.samopil_text{padding:10px;}
	.samopil_left_arrow{width:100%; float:none;}
	.samopil_right_arrow{display:none;}
	.left_samopil_content .samopil_content, .right_samopil_content .samopil_content {height:0; margin: 0; padding:0; overflow:hidden; border:none;}
	.samopil_header_cursor_image{display:block;}
	.samopil_text {float:none; width:100%; margin-top:20px;}
	.samopil_image_left {float:none; width:100%;}
}

</style>
<div style='position:relative; max-width:1100px; margin:auto;'>


<div class='samopil_header'>
	Кто такой?
</div>
<div class='samopil_content'>
	<img src='https://upload.wikimedia.org/wikipedia/commons/2/23/Ninja_The_Last_Thing_You_See.jpg' class='samopil_image_left'/>
	<div class='samopil_text'>
		Выясняйте наименование юридического лица, которое владеет интернет-ресурсом: по законодательству Республики Беларусь все реквизиты должны быть указаны на сайте. 
		Избегайте покупок у невидимок.
	</div>
	<div style='clear:both;'></div>

</div>
	


<div class='samopil_header'>
	Производитель ли
</div>
<div class='samopil_content'>
	<img src='https://cdn.pixabay.com/photo/2016/09/01/10/23/image-1635747_960_720.jpg' class='samopil_image_left'/>
	<div class='samopil_text'>
		Сразу же ставьте под сомнение рекламную фразу «от производителя», потому что:
		<br><br>
		<ul>
			<li>необходимы начальные инвестиции</li>
			<li>содержать производство достаточно затратно</li>
			<li>если представить, что все «производственные» площади разместить рядом, то Минская область будет полностью застроена фабриками и складами с мебелью</li>
		</ul>
	</div>
	<div style='clear:both;'></div>

</div>

<div class='samopil_arrows'>
	<div class='samopil_left_arrow'>
		<img src='https://cdn.icon-icons.com/icons2/1217/PNG/512/1492533499-arrowdown_83273.png'>
	</div>
	<div class='samopil_right_arrow'>
		<img src='https://cdn.icon-icons.com/icons2/1217/PNG/512/1492533499-arrowdown_83273.png'>
	</div>
	<div style='clear:both'></div>
</div>
	
	
<div class='double_samopil_content'>
	
	<div class='left_samopil_content'>
		<div class='samopil_header samopil' data-id='sam'>
			<div class='samopil_header_cursor_image'></div>
			Сам «изготавливаю»…
		</div>
		<div class='samopil_content' id='sam'>
			<img src='http://kuhni-planeta.ru/upload/blog/kuhni-ypala.jpg'/>
			<div class='samopil_text'>
				У вашей кухни будет «много родителей» и каждый будет отвечать за свою часть. Продавец самостоятельно собирает элементы кухни, заказывая их у разных компаний, 
				которые производят распил, изготавливают фасады, импортируют фурнитуру…
			</div>
			<div style='clear:both;'></div>

		</div>
	</div>
	
	
	<div class='right_samopil_content'>
		<div class='samopil_header factory' data-id='diler'>
			<div class='samopil_header_cursor_image'></div>
			Дилер фабрики или фирменный салон
		</div>
		<div class='samopil_content' id='diler'>
			<img src='http://svoimy-rukami.ru/wp-content/uploads/2017/08/dizajn-malenkoj-kuxni-2018-23.jpg'/>
			<div class='samopil_text'>
				В Беларуси существует только несколько крупных фабрик, производителей качественной мебели, которые имеют фирменные салоны и/или работают через дилерскую сеть. 
				Проверяйте дилера на корпоративном сайте фабрики
			</div>
			<div style='clear:both;'></div>

		</div>
	</div>
	<div style='clear:both;'></div>
		
</div>


<div class='samopil_arrows'>
	<div class='samopil_left_arrow'>
		<img src='https://cdn.icon-icons.com/icons2/1217/PNG/512/1492533499-arrowdown_83273.png'>
	</div>
	<div class='samopil_right_arrow'>
		<img src='https://cdn.icon-icons.com/icons2/1217/PNG/512/1492533499-arrowdown_83273.png'>
	</div>
	<div style='clear:both'></div>
</div>


<div class='double_samopil_content'>
	
	<div class='left_samopil_content'>
		<div class='samopil_header samopil' data-id='alien'>
			<div class='samopil_header_cursor_image'></div>
			Фото чужого производства
		</div>
		<div class='samopil_content' id='alien'>
			<img src='https://cs9.pikabu.ru/post_img/big/2018/01/12/6/151574771319731944.jpg'/>
			<div class='samopil_text'>
				Фотографии позаимствованы из интернета, и вас вводят в заблуждение. Уточните, где находится производство, сколько человек там работает, кто руководитель
			</div>
			<div style='clear:both;'></div>

		</div>
	</div>
	
	
	<div class='right_samopil_content'>
		<div class='samopil_header factory' data-id='factory'>
			<div class='samopil_header_cursor_image'></div>
			Сертифицированное производство
		</div>
		<div class='samopil_content' id='factory'>
			<img src='http://russini.ru/upload/medialibrary/390/390ec5db3c382538f53f0e9b83b500a7.jpg'/>
			<div class='samopil_text'>
				В Беларуси строгое законодательство, поэтому все производственные направления проходят жесткий контроль.
			</div>
			<div style='clear:both;'></div>

		</div>
	</div>
	<div style='clear:both;'></div>
		
</div>


<div class='samopil_arrows'>
	<div class='samopil_left_arrow'>
		<img src='https://cdn.icon-icons.com/icons2/1217/PNG/512/1492533499-arrowdown_83273.png'>
	</div>
	<div class='samopil_right_arrow'>
		<img src='https://cdn.icon-icons.com/icons2/1217/PNG/512/1492533499-arrowdown_83273.png'>
	</div>
	<div style='clear:both'></div>
</div>


<div class='double_samopil_content'>
	
	<div class='left_samopil_content'>
		<div class='samopil_header samopil' data-id='danger'>
			<div class='samopil_header_cursor_image'></div>
			Безопасность?
		</div>
		<div class='samopil_content' id='danger'>
			<img src='https://mybookland.ru/wp-content/uploads/2018/08/chestnoe-slovo.jpg'/>
			<div class='samopil_text'>
				Декларация выдается по результатам дорогих лабораторных испытаний. Писем и сертификатов от импортеров фурнитуры не достаточно!
			</div>
			<div style='clear:both;'></div>

		</div>
	</div>
	
	
	<div class='right_samopil_content'>
		<div class='samopil_header factory'  data-id='security'>
			<div class='samopil_header_cursor_image'></div>
			Безопасность подтверждена
		</div>
		<div class='samopil_content' id='security'>
			<img src='https://standartno.by/upload/iblock/303/%D0%9F%D1%80%D0%BE%D0%B5%D0%BA%D1%82%20%D0%94%D0%B5%D0%BA%D0%BB%D0%B0%D1%80%D0%B0%D1%86%D0%B8%D1%8F.jpg'/>
			<div class='samopil_text'>
				Декларация соответствия требованиям ТР ТС «О безопасности мебельной продукции 025/2012. с указанием номера, перечнем моделей и наименованием
			</div>
			<div style='clear:both;'></div>

		</div>
	</div>
	<div style='clear:both;'></div>
		
</div>


<div class='samopil_arrows'>
	<div class='samopil_left_arrow'>
		<img src='https://cdn.icon-icons.com/icons2/1217/PNG/512/1492533499-arrowdown_83273.png'>
	</div>
	<div class='samopil_right_arrow'>
		<img src='https://cdn.icon-icons.com/icons2/1217/PNG/512/1492533499-arrowdown_83273.png'>
	</div>
	<div style='clear:both'></div>
</div>



<div class='double_samopil_content'>
	
	<div class='left_samopil_content'>
		<div class='samopil_header samopil' data-id='nocheck'>
			<div class='samopil_header_cursor_image'></div>
			Договор/Чек/Гарантия?
		</div>
		<div class='samopil_content' id='nocheck'>
			<img src='https://proavtopravo.ru/wp-content/uploads/2017/05/zajavk_o_mochen_police1-1024x640.jpg'/>
			<div class='samopil_text'>
				Должен быть договор с полной стоимостью кухни, чек на всю сумму, инструкция по эксплуатации и гарантийный талон. 
				Если «производитель» обещает решать все вопросы в процессе, бегите от такого продавца прочь
			</div>
			<div style='clear:both;'></div>

		</div>
	</div>
	
	
	<div class='right_samopil_content'>
		<div class='samopil_header factory'  data-id='check'>
			<div class='samopil_header_cursor_image'></div>
			Договор/Чек/Гарантия!
		</div>
		<div class='samopil_content' id='check'>
			<img src='https://kaztag.kz/upload/iblock/c5e/c5e4c88b436d713dac015586411023b2.jpg'/>
			<div class='samopil_text'>
				<ul>
					<li>официальный договор</li>
					<li>чек на всю сумму</li>
					<li>инструкция по эксплуатации</li>
					<li>гарантийный талон</li>
				</ul>
			</div>
			<div style='clear:both;'></div>

		</div>
	</div>
	<div style='clear:both;'></div>
		
</div>


<div class='samopil_arrows'>
	<div class='samopil_left_arrow'>
		<img src='https://cdn.icon-icons.com/icons2/1217/PNG/512/1492533499-arrowdown_83273.png'>
	</div>
	<div class='samopil_right_arrow'>
		<img src='https://cdn.icon-icons.com/icons2/1217/PNG/512/1492533499-arrowdown_83273.png'>
	</div>
	<div style='clear:both'></div>
</div>


<div class='double_samopil_content'>
	
	<div class='left_samopil_content'>
		<div class='samopil_header samopil' data-id='solo'>
			<div class='samopil_header_cursor_image'></div>
			Салон?
		</div>
		<div class='samopil_content' id='solo'>
			<img src='https://proavtopravo.ru/wp-content/uploads/2017/05/zajavk_o_mochen_police1-1024x640.jpg'/>
			<div class='samopil_text'>
				<ul>
					<li>только фото в интернете</li>
					<li>тест маленького фасада</li>
					<li>нет образцов готовых кухонь</li>
					<li>нет гарантии, что продавец не исчезнет с предоплатой</li>
				</ul>
			</div>
			<div style='clear:both;'></div>

		</div>
	</div>
	
	
	<div class='right_samopil_content'>
		<div class='samopil_header factory' data-id='web'>
			<div class='samopil_header_cursor_image'></div>
			Сеть салонов
		</div>
		<div class='samopil_content' id='web'>
			<img src='https://kaztag.kz/upload/iblock/c5e/c5e4c88b436d713dac015586411023b2.jpg'/>
			<div class='samopil_text'>
				<ul>
					<li>серьезный подход</li>
					<li>возможность ознакомиться с экспозицией</li>
					<li>тест фурнитуры</li>
					<li>гарантия</li>
				</ul>
			</div>
			<div style='clear:both;'></div>

		</div>
	</div>
	<div style='clear:both;'></div>
		
</div>


<div class='samopil_arrows'>
	<div class='samopil_left_arrow'>
		<img src='https://cdn.icon-icons.com/icons2/1217/PNG/512/1492533499-arrowdown_83273.png'>
	</div>
	<div class='samopil_right_arrow'>
		<img src='https://cdn.icon-icons.com/icons2/1217/PNG/512/1492533499-arrowdown_83273.png'>
	</div>
	<div style='clear:both'></div>
</div>


<div class='double_samopil_content'>
	
	<div class='left_samopil_content'>
		<div class='samopil_header samopil' data-id='copy'>
			<div class='samopil_header_cursor_image'></div>
			Сделаем то же самое, как на фото
		</div>
		<div class='samopil_content' id='copy'>
			<img src='https://proavtopravo.ru/wp-content/uploads/2017/05/zajavk_o_mochen_police1-1024x640.jpg'/>
			<div class='samopil_text'>
				<ul>
					<li>Подделка</li>
					<li>Вопросы с качеством</li>
					<li>Скрытые дефекты</li>
				</ul>
			</div>
			<div style='clear:both;'></div>

		</div>
	</div>
	
	
	<div class='right_samopil_content'>
		<div class='samopil_header factory' data-id='models'>
			<div class='samopil_header_cursor_image'></div>
			Оригинальные, протестированные модели
		</div>
		<div class='samopil_content' id='models'>
			<img src='https://kaztag.kz/upload/iblock/c5e/c5e4c88b436d713dac015586411023b2.jpg'/>
			<div class='samopil_text'>
				<ul>
					<li>Уникальные модели</li>
					<li>Кухня по индивидуальным размерам</li>
					<li>Проверенные поставщики</li>
					<li>Контроль качества</li>
				</ul>
			</div>
			<div style='clear:both;'></div>

		</div>
	</div>
	<div style='clear:both;'></div>
		
</div>


<div class='samopil_arrows'>
	<div class='samopil_left_arrow'>
		<img src='https://cdn.icon-icons.com/icons2/1217/PNG/512/1492533499-arrowdown_83273.png'>
	</div>
	<div class='samopil_right_arrow'>
		<img src='https://cdn.icon-icons.com/icons2/1217/PNG/512/1492533499-arrowdown_83273.png'>
	</div>
	<div style='clear:both'></div>
</div>


<div class='double_samopil_content'>
	
	<div class='left_samopil_content'>
		<div class='samopil_header samopil' data-id='saving'>
			<div class='samopil_header_cursor_image'></div>
			Сомнительная экономия
		</div>
		<div class='samopil_content' id='saving'>
			<div class='samopil_text'>
				За счет чего стоимость кухни у ИП может быть меньше, чем предложенная дилером фабрики?
				<br>
				<ul>
					<li>Неофициальная оплата, отсутствие договора, уход от налогов = ваш риск потери денег, отсутствие гарантии на товар</li>
					<li>Подмена фурнитуры на более низкую по качеству во всей кухне либо в некоторых шкафах</li>
					<li>Использование ЛДСП меньшей толщины</li>
					<li>Экономия на профессиональных установщиках</li>
					<li>Экономия на вашем здоровье и безопасности, отсутствие Декларации соответствия</li>
					<li>Подделка фасадов, использование более дешевой конструкции либо покрытия</li>
					<li>Экономия на кромочных материалах, использование «старинных» методов нанесения кромки</li>
				</ul>
			</div>
			<div style='clear:both;'></div>

		</div>
	</div>
	
	
	<div class='right_samopil_content'>
		<div class='samopil_header factory' data-id='cost'>
			<div class='samopil_header_cursor_image'></div>
			Разумная цена
		</div>
		<div class='samopil_content' id='cost'>
			<div class='samopil_text'>
				Ни одна из белорусских фабрик сейчас не получает сверхприбыль, поэтому цены очень привлекательные, а с учетом регулярных скидок вообще заманчивые. 
				Кроме того есть много преимуществ:
				<br>
				<ul>
					<li>Действующие салоны с образцами</li>
					<li>Индивидуальный заказ</li>
					<li>Четкие сроки выполнения заказа</li>
					<li>Официальные документы на кухню</li>
					<li>Профессиональные дизайнеры</li>
					<li>Гарантия от 2 лет</li>
					<li>Опыт более 10 лет, а нашей фабрике -28!!!</li>
					<li>Проверенные поставщики</li>
					<li>Современное оборудование</li>
					<li>Сертификация</li>
					<li>Качественная фурнитура</li>
				</ul>
			</div>
			<div style='clear:both;'></div>

		</div>
	</div>
	<div style='clear:both;'></div>
		
</div>
<br><br>
Помните, что кухня – это самое затратное помещение в доме, здесь используется много бытовой техники, модули и поверхности несут повышенные нагрузки, 
при установке проводятся сложные монтажные работы. Проще заменить диван или кровать, чем вносить корректировки в уже установленный гарнитур. 
Сравнивайте цены и не экономьте на важном
	
</div><br><br><br><br>

<script>
jQuery('.samopil_header').click(function() {
	var thisdataid = jQuery( this ).attr('data-id');
	var thisid = document.getElementById(thisdataid);
	jQuery(thisid).css({'height':'auto', 'margin':'10px auto 20px auto', 'border':'1px solid #dcdcdc'});
});
</script>

";

	return $a;

}

function samples_cities_window()
{

	global $wpdb;
	$template_url = get_template_directory_uri();
	$a = "	

	<div id='samples_city_bg' class='samples_city_bg'>
		<div class='samples_city_div'>
			<div id='samples_city_close' class='samples_city_close'>
				<img src='$template_url/images/close.png' style='width:100%; height:100%;' alt='закрыть'/>
			</div>
			<div id='stabs' class='s-tabs s-no-js'>
					<div class='s-tabs-nav'>

	";
	//Смотрим страну в ip
	$country_ip = "Беларусь";
	$sqlCountry = "SELECT DISTINCT country FROM gi_salons";
	$resultCountry = $wpdb->get_results($sqlCountry);
	$b = '';
	foreach ($resultCountry as $rowCountry) {
		$country = $rowCountry->country;
		if ($country_ip == $country) {
			$is_active = "s-is-active";
		} else
			$is_active = "";
		$a .= "
				<a href='#' class='s-tabs-nav__link $is_active'>
					<span>$country</span>
				</a>
		";
		$b .= "
			<div class='s-tab $is_active'>
				<p class='h2' style='margin:32px 0 32px 0;'>$country</p>
				<div class='s-tab__content'>
		";
		//ПОЛУЧАЕМ ГОРОДА
		$sqlCities = "SELECT DISTINCT city FROM gi_samples WHERE salon_address LIKE '%$country%' AND moderate ='yes' ORDER BY city";
		$resultCities = $wpdb->get_results($sqlCities);
		foreach ($resultCities as $rowCities) {
			$city = $rowCities->city;
			//ТЕПЕРЬ ПОЛУЧАЕМ ССЫЛКУ НА СТРАНИЦУ САЛОНА
			switch ($country) {
				case 'Россия':
					$sampleslink = "samples-russia/";
					break;
				case 'Беларусь':
					$sampleslink = "samples-belarus/";
					break;
				case 'Украина':
					$sampleslink = "samples-ukraine/";
					break;
				case 'Казахстан':
					$sampleslink = "samples-kazakhstan/";
					break;
				default:
					$sampleslink = "samples-russia/";
					break;
			}
			$link = "/prodazha-obrazcov/$sampleslink?city=$city";
			$b .= "<a href='$link' class='citiesRow' style=''>$city</a>";

		}
		$b .= "
			</div>
		</div>
		";

	}
	$a .= "</div>";
	$a .= "$b";

	$a .= "
			</div>
		</div>
	</div>
	<style>
	.citiesRow{font-size:16px; display:block;}
	</style>
	<script>
	var samples_city_close = document.getElementById('samples_city_close');
	var samples_city_bg = document.getElementById('samples_city_bg');
	var samples_city_select = document.getElementById('samples_city_select');
	var samples_city_select2 = document.getElementById('samples_city_select2');
	var samples_city_select3 = document.getElementById('menu-item-1042');
	samples_city_select3.onclick = function(){
		samples_city_bg.style.display='block';
	}
	if(samples_city_select){
		samples_city_select.onclick = function(){
			samples_city_bg.style.display='block';
		}
	}
	if(samples_city_select2){
		samples_city_select2.onclick = function(){
			samples_city_bg.style.display='block';
		}
	}
	samples_city_close.onclick = function(){
		samples_city_bg.style.display='none';
	}
	
	
	
	</script>	
	";
	return $a;
}

function cities_window()
{
	global $wpdb;
	$template_url = get_template_directory_uri();
	$a = "	

	<div id='gde_zakazat_bg' class='gde_zakazat_bg' style=''>
		<div class='gde_zakazat_div' style=''>
			<div id='gde_zakazat_close' class='gde_zakazat_close' style=''>
				<img src='$template_url/images/close.png' style='width:100%; height:100%;' alt='close'/>
			</div>
			<div id='tabs' class='c-tabs no-js'>
					<div class='c-tabs-nav'>

	";
	$content = '';
	//Смотрим страну в ip
	$country_ip = "Беларусь";
	$sqlCountry = "SELECT country, COUNT(*) as cntry_count 
	FROM gi_salons 
	WHERE moderate = 'yes' 
		AND country NOT IN ('Украина', 'Новый') 
	GROUP BY country 
	ORDER BY FIELD(country, 'ОАЭ', 'Польша', 'Казахстан', 'Россия', 'Беларусь') DESC";
	$resultCountry = $wpdb->get_results($sqlCountry);
	foreach ($resultCountry as $rowCountry) {
		$country = $rowCountry->country;
		$cntry_count = $rowCountry->cntry_count;
		if ($country_ip == $country) {
			$is_active = "is-active";
		} else
			$is_active = "";
		$a .= "
				<a href='#' class='c-tabs-nav__link $is_active'>
					<span>$country ($cntry_count)</span>
				</a>
		";
		$b = "
			<div class='c-tab $is_active'>
				<p class='h2' style='margin:32px 0 32px 0;'>$country</p>
				<div class='c-tab__content'>
		";



		$rus = array(' ', 'А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я', 'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я', ' ');
		$lat = array('_', 'a', 'b', 'v', 'g', 'd', 'e', 'e', 'gh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'ch', 'sh', 'sch', 'y', 'y', 'y', 'e', 'yu', 'ya', 'a', 'b', 'v', 'g', 'd', 'e', 'e', 'gh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'ch', 'sh', 'sch', 'y', 'y', 'y', 'e', 'yu', 'ya', ' ');

		$all_cities = array();
		$strannie_bukvi = array("Ą", "ą", "Ć", "ć", "Ę", "ę", "Ł", "ł", "Ń", "ń", "Ó", "ó", "Ś", "ś", "Ź", "ź", "Ż", "ż");
		$normalnie_bukvi = array("A", "a", "C", "c", "E", "e", "L", "l", "N", "n", "O", "o", "S", "s", "Z", "z", "Z", "z");

		$sqlCities = "SELECT city, Count(*) as count FROM gi_salons WHERE country = '$country' AND moderate ='yes' GROUP BY city ORDER BY city ASC";
		$resultCities = $wpdb->get_results($sqlCities);
		foreach ($resultCities as $rowCities) {
			$city = $rowCities->city;
			$count = $rowCities->count;
			if ($city == "Москва" or $city == "Минск" or $city == "Киев" or $city == "Санкт-Петербург") {
				$weight = "font-weight:900;";
			} else {
				$weight = "font-weight:200;";
			}

			$alias = mb_strtolower(str_replace($strannie_bukvi, $normalnie_bukvi, $city));
			$alias = str_replace($rus, $lat, $alias);

			$b .= "<a href='/gde-zakazat/$alias/' class='citiesRow' style='$weight'>$city ($count)</a>";
		}


		/*
			//ПОЛУЧАЕМ ГОРОДА
			$sqlCities = "SELECT DISTINCT city FROM gi_salons WHERE country = '$country' AND moderate ='yes' ORDER BY city";
			$resultCities = $wpdb->get_results($sqlCities);
			foreach($resultCities as $rowCities){
				$city = $rowCities->city;
				if($city == "Москва" or $city == "Минск" or $city == "Киев" or $city == "Санкт-Петербург") {$weight = "font-weight:900;";}
				else {$weight = "font-weight:200;";}
				
				//ТЕПЕРЬ ПОЛУЧАЕМ ССЫЛКУ НА СТРАНИЦУ САЛОНА
				$sqlLink = "SELECT ID FROM wp_posts WHERE post_title = '$city' AND post_type='page'";
				$resultLink = $wpdb->get_results($sqlLink);
				foreach ($resultLink as $rowLink){
					$id = $rowLink -> ID;
					$link = get_permalink($id);
					
					$b.="<a href='$link' class='citiesRow' style='$weight'>$city</a>";
				}
				
			}
			*/
		$b .= "
		</div>
	</div>
	";
		$content .= $b;
	}
	$a .= "</div>";
	$a .= $content;

	$a .= "
			</div>
		</div>
	</div>
	<style>
	.citiesRow{font-size:16px; display:block;}
	</style>	
	";
	return $a;
}

function open_full_map()
{
	$a = "
	<a href='#fullMapDiv' id='openMapBtn'><button class='myBtn'>Все салоны на карте</button></a>
	";
	$a .= "
	<div id='fullMapDiv' style='width:100%; height:0; background:yellow; overflow-y:hidden; transition:0.7s ease;'>
		<div style='width:100%; height:500px; background:blue; position:relative;'>
		
			<div id='fullMapCloseArrow' style='position:absolute; width:120px; height:40px; bottom:0; left:calc(50% - 60px); background:white; cursor:pointer; text-align:center; line-height:40px; font-size:32px;'>
				<i class='fa fa-icon fa-angle-up' style='font-size:42px;'></i>
			</div>
		</div>
	
	</div>
	";

	$a .= "
	<script>
	var openMapBtn = document.getElementById('openMapBtn');
	var fullMapDiv = document.getElementById('fullMapDiv');
	var fullMapCloseArrow = document.getElementById('fullMapCloseArrow');
 
jQuery(document).ready(function() {
  jQuery('#openMapBtn').click(function() {
    var elementClick = jQuery(this).attr('href');
    var destination = jQuery(elementClick).offset().top;
    jQuery('html:not(:animated),body:not(:animated)').animate({
      scrollTop: destination - 55
    }, 700);
	jQuery('#fullMapDiv').css('height', '500px');

    return false;
  });
});

	
	fullMapCloseArrow.onclick = function (){
		fullMapDiv.style.height='0px';
	}
	</script>
	";

	return $a;
}

?>