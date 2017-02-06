<?php
if(!empty($_GET)){
	$school=$_GET['school'];
	switch($school){
	case 1:
    $img="pic/1.jpg";
    break;	
	case 2:
    $img="pic/2.jpg";
    break;	
	case 3:
    $img="pic/3.jpg";
    break;	
	case 4:
    $img="pic/4.jpg";
    break;
case 5:
    $img="pic/5.jpg";
    break;	
case 6:
    $img="pic/6.jpg";
    break;	
case 7:
    $img="pic/7.jpg";
    break;	
case 8:
    $img="pic/8.jpg";
    break;	
case 9:
    $img="pic/9.jpg";
    break;	
case 10:
    $img="pic/10.jpg";
    break;	
case 11:
    $img="pic/11.jpg";
    break;	
case 12:
    $img="pic/12.jpg";
    break;	
case 13:
    $img="pic/13.jpg";
    break;	
case 14:
    $img="pic/14.jpg";
    break;	
case 15:
    $img="pic/15.jpg";
    break;	
case 16:
    $img="pic/16.jpg";
    break;	
case 17:
    $img="pic/17.jpg";
    break;	
case 18:
    $img="pic/18.jpg";
    break;	
case 19:
    $img="pic/19.jpg";
    break;	
case 20:
    $img="pic/20.jpg";
    break;	
case 21:
    $img="pic/21.jpg";
    break;	
case 22:
    $img="pic/22.jpg";
    break;	
case 23:
    $img="pic/23.jpg";
    break;		
case 24:
    $img="pic/24.jpg";
    break;	
	case 25:
    $img="pic/25.jpg";
    break;	
	default:
	$img="pic/1.jpg";
    break;	
		
	}
}else{
	 $img="pic/0.jpg";
	 $school="0";
}

?>
<!doctype html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>口袋高校</title>
		<meta name = "viewport" content = "initial-scale = 1, user-scalable = no">
		<style>
			body{
				margin: 0;
				padding:0;
			}
		/***********banner***********/
			.banner{
				width:100%;
				background-color: #2c6;
				text-align: center;
			}
			.image{
				width:50%;
				margin: 20px auto;
			}
		/***********head***********/
			h3{
				text-align: center;
				color:#555;
				font-family: '微软雅黑';
			}
			.head{
				width:90%;
				margin: -2px auto;
			}
			.left,.right{
				margin-top:8px;
				width: 35%;
				background-color: #bbb;
				height:1px;
				float:left;
			}
			.cet{
				width:30%;
				font-size:0.4em ;
				font-family:'Microsoft Yahei';
				text-align: center;
				float:left;
				color:#bbb;
			}		
		/***********user***********/
			.school{
				text-align: left;
				margin-top:70px;
				text-align: center;
			}
			.school select{
				background-color: #eee;
				width:75%;
				height:50px;
				font:1em 'Microsoft Yahei';
				color:#888;
				border:1px solid white;
				border-radius:10px;
				padding-left:15px;
				outline:none;
			}
		/***********qr-code***********/
			.qr-code{
				text-align: center;
			}
			.qr-code img{
				width:60%;
				margin-top:25px;
			}
		/***********marker***********/
			.marker p{
				text-align: center;
				font:1.2em 'Microsoft Yahei';
				color:#2b6;
			}
		/***********technology***********/
			.technology p{
				font:0.5em '微软雅黑';
				color:#bbb;
				margin: 0;
			}
			.technology{
				text-align: center;
				position:absolute;
				bottom:0;
				left:0;
				right:0;
			}
		</style>
	</head>
	<body>
	<!-- banner -->
		<div class="banner">
			<img class="image" src="pic/logo.png" alt="">
		</div>
	<!-- head -->
		<h3>传送门</h3>
		<div class="head">
			<div class="left"></div>
			<div class="cet">PocketUniversity</div>
			<div class="right"></div>
		</div>
	<!-- school -->
	<form action="" method="get">
		<div class="school">
			<select name="school" onchange="submit();">
				<option value="0"<?php if($school==0) echo "selected=\"selected\""?>>选择学校</option>
				<option value="1" <?php if($school==1) echo "selected=\"selected\""?>>杭州电子科技大学</option>
				<option value="2"<?php if($school==2) echo "selected=\"selected\""?>>浙江师范大学</option>
				<option value="3"<?php if($school==3) echo "selected=\"selected\""?>>浙江财经大学</option>
				<option value="4"<?php if($school==4) echo "selected=\"selected\""?>>浙江传媒学院</option>
				<option value="5"<?php if($school==5) echo "selected=\"selected\""?>>浙江理工大学</option>
				<option value="6"<?php if($school==6) echo "selected=\"selected\""?>>浙江工商大学</option>
				<option value="7"<?php if($school==7) echo "selected=\"selected\""?>>中国计量学院</option>
				<option value="8"<?php if($school==8) echo "selected=\"selected\""?>>杭州师范大学</option>
				<option value="9"<?php if($school==9) echo "selected=\"selected\""?>>浙江大学</option>
				<option value="10"<?php if($school==10) echo "selected=\"selected\""?>>浙江工业大学</option>
				<option value="11"<?php if($school==11) echo "selected=\"selected\""?>>中国美术学院</option>
				<option value="12"<?php if($school==12) echo "selected=\"selected\""?>>宁波大学</option>
				<option value="13"<?php if($school==13) echo "selected=\"selected\""?>>杭州职业技术学院</option>
				<option value="14"<?php if($school==14) echo "selected=\"selected\""?>>浙江中医药大学</option>
				<option value="15"<?php if($school==15) echo "selected=\"selected\""?>>浙江育英职业技术学院</option>
				<option value="16"<?php if($school==16) echo "selected=\"selected\""?>>浙江水利水电专科学校</option>
				<option value="17"<?php if($school==17) echo "selected=\"selected\""?>>浙江农林大学</option>
				<option value="18"<?php if($school==18) echo "selected=\"selected\""?>>浙江科技学院</option>
				<option value="19"<?php if($school==19) echo "selected=\"selected\""?>>浙江警官职业学院</option>
				<option value="20"<?php if($school==20) echo "selected=\"selected\""?>>浙江经贸职业技术学院</option>
				<option value="21"<?php if($school==21) echo "selected=\"selected\""?>>浙江经济职业技术学院</option>
				<option value="22"<?php if($school==22) echo "selected=\"selected\""?>>浙江金融职业学院</option>
				<option value="23"<?php if($school==23) echo "selected=\"selected\""?>>浙江大学城市学院</option>
				<option value="24"<?php if($school==24) echo "selected=\"selected\""?>>丽水学院</option>
				<option value="25"<?php if($school==25) echo "selected=\"selected\""?>>西北师范大学</option>
			</select>
		</div>
		</form>
	<!-- qr code -->
		<div class="qr-code">
			<img src="<?php echo $img?>" alt="">
		</div>
	<!-- marker -->
		<div class="marker">
			<p>长按二维码，识别后即可关注</p>
		</div>
	<!-- technology -->
		<div class="technology">
			<p>@ power by 口袋高校</p>
		</div>

	</body>
</html>
