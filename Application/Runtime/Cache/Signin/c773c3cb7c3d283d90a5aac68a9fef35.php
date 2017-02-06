<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
  <head>    
    <meta charset="UTF-8">   
    <meta name="author" content="Sure" email="Sure@tuiyilin.com"> 
    <meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no"> 
    <meta content="yes" name="apple-mobile-web-app-capable" />
    <meta content="black" name="apple-mobile-web-app-status-bar-style" />
    <meta content="telephone=no" name="format-detection" />
    <title>早起打卡</title><!-- 后期可自定义 --> 
    <!-- 应用的样式依赖 start-->   
    <link rel="stylesheet" href="http://static.pocketuniversity.cn/kdgx/EveryDay/1.0/css/lib/weui.min.css"> 
	<link rel="stylesheet" href="http://static.pocketuniversity.cn/kdgx/EveryDay/1.0/css/main.css">
	<!-- 应用的样式依赖 end-->   
  </head>  
  <body>    
<!--   	<div class="wrapHide"> 
  		<p>系统正在全面升级，<br>预计在晚上19:00完成<br>即将以全新的面貌呈现，<br>小伙伴们请期待...</p>
  	</div>  -->
    <div class="page" id="page">   
    	<div class="appWrap appReady">   
    		<div class="appLogo">  
    			<img src="http://static.pocketuniversity.cn/kdgx/EveryDay/1.0/images/appLoading.gif" alt="早起打卡" width="100" height="100">
    		</div>
    	</div> 
	  <!-- 应用启动动画 start-->
   	  <div class="appContent">   
   	  	<div class="js_dialog" @click="cancleInvite" id="iosDialog3" v-show="inviteState" style="opacity:1;">
   	  	    <div class="weui-mask"></div>
   	  	    <div class="sharePoint2"> 
   	  	        <img src="http://static.pocketuniversity.cn/kdgx/EveryDay/1.0/images/wxShare.png" alt="" width="40" height="auto">
   	  	        <p>分享给TA，提醒TA打卡</p>  
   	  	    </div>   
   	  	</div> 
    	<!-- 打卡的动态显示 start--> 
		<div class="mark">  
			<div class="signed" v-if="state == 2"> 
				<div class="mark-head"> 
					<h3>恭喜获得: <span @click="cardShow" style="color:rgb(31,204,124)">成就卡</span></h3>   
				</div>   
				<div class="mark-button">  
					<img src="http://static.pocketuniversity.cn/kdgx/EveryDay/1.0/images/marked.png" alt="早起签到" width="120" height="auto">
				</div>   
				<p v-if="0">于 <span v-text="nowDate | timer"></span> 起床</p>  
				<p v-if="1">  
					<span>本次共获得</span><span v-text="energy"></span><span>正能量</span>  
					<span style="margin-left:10px;"><a style="color:rgba(65,176,239,1)" href="http://static.pocketuniversity.cn/kdgx/mooddiary/intro.html">积分规则</span>  
				</p>  
			</div>   
			<div class="mark-button" id="stateOne" v-show="state == 1 || state == 0">   
				<h3 class="mark-head">打卡留住每一次改变</h3>
				<h4 class="scoreRuler"><a style="color:rgb(31,204,124)" href="http://static.pocketuniversity.cn/kdgx/mooddiary/intro.html">点此查看积分规则</a></h4>  
			    <img src="http://static.pocketuniversity.cn/kdgx/EveryDay/1.0/images/mark-info.png" alt="签到记录留住每一次改变" width="150" height="auto">
			    <div class="showKeywords" v-if="!outTime">
			        <a @click="showKeywords" class="weui-dialog__btn weui-dialog__btn_default">点此打卡</a> 
			    </div>
			    <div class="showKeywords" v-if="outTime">
			        <a @click="timeMessage" class="weui-dialog__btn weui-dialog__btn_default" v-text="isOutTime"></a>
			    </div>  
			</div> 
		</div>  
		<!-- 打卡的动态显示 end-->  
		<div class="wrap"><!-- 预留遮罩层 -->  
			<!-- 每周统计 start--> 
			 
			<div class="dayScore" v-if="topTime"> 
			  	<a :href="'http://'+ pocketHost +'/index.php/Signin/Mood/index?media_id=' + media_id">
			  	<div class="box">  
					<div class="dayScore-head">  
						<span>生长足迹</span> 
						<span class="dayMoods">已坚持<i v-text="totalTimes"></i>天</span> 
					</div>
				<!-- <div class="dayScore-body">
					<div class="day" v-for="state in dayScore.states">
						<span v-text="state.timestamp | thatDay"></span>
						<span v-bind:class="{'beforeMark':state.state == 0,'afeterMark':state.state == 1}" v-text="state.timestamp | everyDate"></span>
					</div>   
				</div>    -->    
					<div class="dayScore-body">   
						<div class="day" v-for="mood in dayMoods.lists">  
							<span v-text="mood.time | thatDay"></span>  
							<span v-if="mood.mood == 0" v-bind:class="{'beforeMark':mood.signin == 0,'afeterMark':mood.signin == 1}" v-text="mood.time | everyDate"></span>
							<span v-if="mood.mood != 0" class="emoji" v-bind:class="{'mood_1':mood.mood == 1,'mood_2':mood.mood == 2,'mood_3':mood.mood == 3,'mood_4':mood.mood == 4}"></span>      
						</div>    
					</div>
				</div></a>  
				<div></div>  
				<!-- <a :href="'http://www.pocketuniversity.cn/index.php/Signin/Mood/index?media_id=' + media_id + '#write'"> -->
				<a :href="'http://'+ pocketHost +'/index.php/Signin/Mood/index?media_id=' + media_id">
					<div class="dayScore-foot"><span>心情日记</span><!-- <i class="interest">打卡,不在于多早,在于习惯的乐趣</i> --></div>  
				<a/> 
			</div>  
			<!-- 每周统计 end--> 


			<!-- 定时备份代码片	 --> 
			<div class="dayScore" v-if="!topTime"> 
			  	<div class="box">  
					<div class="dayScore-head">  
						<span>生长足迹</span> 
						<span class="dayMoods">已坚持<i v-text="totalTimes"></i>天</span>  
					</div>
					<div class="dayScore-body">   
						<div class="day" v-for="mood in dayMoods.lists">  
							<span v-text="mood.time | thatDay"></span>  
							<span v-if="mood.mood == 0" v-bind:class="{'beforeMark':mood.signin == 0,'afeterMark':mood.signin == 1}" v-text="mood.time | everyDate"></span>
							<span v-if="mood.mood != 0" class="emoji" v-bind:class="{'mood_1':mood.mood == 1,'mood_2':mood.mood == 2,'mood_3':mood.mood == 3,'mood_4':mood.mood == 4}"></span>     
						</div>    
					</div>
				</div> 
				<div></div> 
			</div>
			<!-- 定时备份代码片	 --> 


			<!-- 榜单排行 start-->  
			<div class="totalRank">  
				<div class="rank-head">    
					<h3 id="fourRanks">  
						<span @click="getFriends" class="theRank">好友榜</span>
						<span @click="getDayRanks" class="theRank tabRank">早起榜</span>
						<span @click="showPublic" class="theRank">全校榜</span>
						<span @click="getAllSchools" class="theRank">高校榜</span>
						<!-- <span class="theRank">高校榜</span> -->

						<!-- <span class="theRank tabRank">好友榜</span> -->
						<!-- <span class="theRank">早起榜</span> -->
						<!-- <span @click="friendLists" class="allFriends">好友榜</span> -->
						<!-- <span class="theRank">全校榜</span> -->
						<!-- <span class="theRank">高校榜</span> -->
					</h3>
				</div> 
				<!-- 好友排行榜-->  
				<div class="rank-body" id="friendLists" v-show="tab_b">
					<div class="joinUs">  
						<p v-if="!friends.lists[0]" class="have"> 
							<a :href="'http://' + pocketHost + '/index.php/Signin/share/friends?uid=' + myUid + '&media_id=' + media_id">
								<span class="addFriends">邀请好友</span>
							</a>
							<span class="addTips">当前你还没有好友打卡</span> 
						</p>  
						<p v-if="friends.lists[0]" class="haveNot">
							<a :href="'http://' + pocketHost + '/index.php/Signin/share/friends?uid=' + myUid + '&media_id=' + media_id">
								<span class="addFriends">邀请好友</span> 
							</a>
							<span class="addTips">邀请好友一起打卡吧</span>
						</p>
					</div>
					<div class="rank-list" v-for="list in friends.lists"> 
						<!-- <div class="list-content" v-if="$index == 0 && list.ranking == 0">
							<span style="color:rgb(1, 211, 204)" class="rankNum">无</span>
							<span class="headImg"><div class="vip_icon"></div><img :src="list.headimgurl | noHeadImg" alt="" width="30" height="auto"></span>
							<span style="color:rgb(1, 211, 204)" class="nickName" v-text="list.nickname | noNickName"></span>
							<span style="color:rgb(1, 211, 204)" class="times" style="font-size:1rem;">未上此榜</span>
						</div>
						<div class="list-content" v-if="$index == 0 && list.ranking != 0"> 
							<span style="color:rgb(1, 211, 204)" class="rankNum" v-text="$index+1"></span>
							<span class="headImg"><div class="vip_icon"></div><img :src="list.headimgurl | noHeadImg" alt="" width="30" height="auto"></span>
							<span style="color:rgb(1, 211, 204)" class="nickName" v-text="list.nickname | noNickName"></span>
							<span style="color:rgb(1, 211, 204)" class="times">已签<i v-text="list.count"></i>次</span>
							<span style="color:rgb(1, 211, 204)" class="energy"><i v-text="list.score"></i>g</span>
						</div> --> 
						<div class="list-content">       
							<span v-if="list.timestamp != lastTime" v-bind:class="{ 'rankToper': $index == 0|| $index == 1 || $index == 2}" class="rankNum" v-text="$index+1"></span>
							<span v-if="list.timestamp == lastTime" v-bind:class="{ 'lazyDog':list.timestamp == lastTime }" class="rankNum"></span>
							<span class="headImg"> 
								<div class="vip_icon" v-bind:class="{'vip_0':list.vip == 0,'vip_1':list.vip == 1,'vip_2':list.vip == 2,'vip_3':list.vip == 3,'vip_4':list.vip == 4,'vip_5':list.vip == 5}"></div><img :src="list.headimgurl | noHeadImg" alt="" width="30" height="auto">
							</span>  
							<span class="nickName" v-bind:class="{ 'vip_color_0':list.vip == 0,'vip_color_1':list.vip == 1,'vip_color_2':list.vip == 2,'vip_color_3':list.vip == 3,'vip_color_4':list.vip == 4,'vip_color_5':list.vip == 5}" v-text="list.nickname | noNickName"></span>
							<span v-if="list.timestamp != lastTime" v-bind:class="{ 'rankToper': $index == 0|| $index == 1 || $index == 2,'markTime_0':list.color == 0,'markTime_1':list.color == 1,'markTime_2':list.color == 2,'markTime_3':list.color == 3}" class="times"><i v-text="list.timestamp | timer"></i> (+<i v-text="list.dayscore"></i>g)</span>
							<span v-if="list.timestamp == lastTime" v-bind:class="{ 'rankToper': $index == 0|| $index == 1 || $index == 2,'markTime_0':list.color == 0,'markTime_1':list.color == 1,'markTime_2':list.color == 2,'markTime_3':list.color == 3}" class="times"><i>未打卡</i><b class="notice" @click="invite">点此提醒TA</b></span>  
							<span class="energy" v-bind:class="{ 'vip_color_0':list.vip == 0,'vip_color_1':list.vip == 1,'vip_color_2':list.vip == 2,'vip_color_3':list.vip == 3,'vip_color_4':list.vip == 4,'vip_color_5':list.vip == 5}"><i v-text="list.score | myEnergy"></i></span>
							<!-- <span v-if="list.score != 0" v-bind:class="{ 'rankToper': $index == 0|| $index == 1 || $index == 2}" class="energy"><i v-text="list.score"></i>g</span>
							<a :href="'/index.php/Signin/share/friends?uid=' + myUid + '&media_id=' + media_id">
								<span v-if="list.score == 0" class="addFriends energy" style="width:40px;height:24px;margin-top:3px;line-height:24px;">邀请</span> 
							</a> --> 
						</div>
					</div>
				</div>
				<!-- 早起榜单排行--> 
				<div class="rank-body" id="dayLists" v-show="tab_a">
					<div class="rank-list" v-for="list in dayRank.lists">
						<div class="list-content" v-if="$index == 0 && list.ranking == 0">
							<span style="width:32px;color:rgb(1, 211, 204)" class="rankNum">无</span>
							<span class="headImg">
								<span class="headImg">
									<div class="vip_icon" v-bind:class="{'vip_0':list.vip == 0,'vip_1':list.vip == 1,'vip_2':list.vip == 2,'vip_3':list.vip == 3,'vip_4':list.vip == 4,'vip_5':list.vip == 5}"></div><img :src="list.headimgurl | noHeadImg" alt="" width="30" height="auto">
								</span> 
							</span>
							<span style="max-width:100px;" class="nickName" v-bind:class="{ 'vip_color_0':list.vip == 0,'vip_color_1':list.vip == 1,'vip_color_2':list.vip == 2,'vip_color_3':list.vip == 3,'vip_color_4':list.vip == 4,'vip_color_5':list.vip == 5}" v-text="list.nickname | noNickName"></span>
							<span class="energy" style="margin-left:5px;float:left;padding-left:2px;color:rgb(1, 211, 204)">+<i v-text="list.dayscore"></i>g</span> 
							<span style="float:right;color:rgb(1, 211, 204)" class="markTime">未上此榜</span> 
						</div> 
						<div class="list-content" v-if="$index == 0 && list.ranking != 0">
							<span style="width:32px;color:rgb(1, 211, 204)" class="rankNum" v-text="list.ranking"></span>
							<span class="headImg">
								<div class="vip_icon" v-bind:class="{'vip_0':list.vip == 0,'vip_1':list.vip == 1,'vip_2':list.vip == 2,'vip_3':list.vip == 3,'vip_4':list.vip == 4,'vip_5':list.vip == 5}"></div><img :src="list.headimgurl | noHeadImg" alt="" width="30" height="auto">
							</span> 
							<span style="max-width:100px;" class="nickName" v-bind:class="{ 'vip_color_0':list.vip == 0,'vip_color_1':list.vip == 1,'vip_color_2':list.vip == 2,'vip_color_3':list.vip == 3,'vip_color_4':list.vip == 4,'vip_color_5':list.vip == 5}" v-text="list.nickname | noNickName"></span>
							<span class="energy" style="margin-left:5px;float:left;padding-left:2px;color:rgb(1, 211, 204)">+<i v-text="list.dayscore"></i>g</span>
							<span style="float:right;color:rgb(1, 211, 204)" class="markTime" v-text="list.timestamp | timer"></span>
						</div>
						<div class="list-content" v-if="$index != 0">
							<span style="width:32px;" v-bind:class="{ 'rankToper': $index == 1|| $index == 2 || $index == 3}" class="rankNum" v-text="list.ranking"></span>
							<span class="headImg"> 
								<div class="vip_icon" v-bind:class="{'vip_0':list.vip == 0,'vip_1':list.vip == 1,'vip_2':list.vip == 2,'vip_3':list.vip == 3,'vip_4':list.vip == 4,'vip_5':list.vip == 5}"></div><img :src="list.headimgurl | noHeadImg" alt="" width="30" height="auto">
							</span>   
							<span style="max-width:100px;" class="nickName" v-bind:class="{ 'vip_color_0':list.vip == 0,'vip_color_1':list.vip == 1,'vip_color_2':list.vip == 2,'vip_color_3':list.vip == 3,'vip_color_4':list.vip == 4,'vip_color_5':list.vip == 5}" v-text="list.nickname | noNickName"></span>
							<span class="energy" v-bind:class="{ 'rankToper': $index == 1|| $index == 2 || $index == 3}"  style="margin-left:5px;float:left;padding-left:2px;">+<i v-text="list.dayscore"></i>g</span>
							<span style="float:right;" v-bind:class="{ 'rankToper': $index == 1|| $index == 2 || $index == 3}" class="markTime" v-text="list.timestamp | timer"></span>
						</div>
					</div>  
					<div class="rank-foot">   
						<p class="dayRankTips" style="font-size:.8rem;text-align:center;line-height:30px;">今天前100名可上此榜，每早05:00排名重置</p> 
					</div>    
				</div>  
				<!-- 高校榜单排行-->   
				<div class="rank-body" id="allCountry" v-show="tab_d"> 
					<div class="joinUs joinSchool">   
						<p class="haveNot">
							<a :href="'http://' + pocketHost + '/index.php/Signin/share/school?uid=' + myUid + '&media_id=' + media_id">
								<span class="addFriends addSchools">为母校攒能量</span>  
							</a>
							<span class="addTips schoolTips">寒假百校风云榜<i>(1/15-2/15)</i></span> 
							<!-- <span class="addTips schoolTips" style="float:none;display:block;margin:0 auto;width:210px;text-align:center;">寒假百校风云榜<i>(1/15-2/15)</i></span> -->
						</p> 
					</div>    
					<div class="rank-list" v-for="list in schoolRank.lists">  
<!-- 						<div class="list-content" v-if="$index == 0 && list.ranking == 0">
							<span style="color:rgb(1, 211, 204)" class="rankNum">无</span>
							<span class="headImg"><img :src="list.headimgurl | noHeadImg" alt="" width="30" height="auto"></span>
							<span style="color:rgb(1, 211, 204)" class="nickName" v-text="list.nickname | noNickName"></span>
							<span style="color:rgb(1, 211, 204)" class="times" style="font-size:1rem;">未上此榜</span>
						</div>
						<div class="list-content" v-if="$index == 0 && list.ranking != 0"> 
							<span style="color:rgb(1, 211, 204)" class="rankNum" v-text="list.ranking"></span>
							<span class="headImg"><img :src="list.headimgurl | noHeadImg" alt="" width="30" height="auto"></span>
							<span style="color:rgb(1, 211, 204)" class="nickName" v-text="list.nickname | noNickName"></span>
							<span style="color:rgb(1, 211, 204)" class="times">已签<i v-text="list.count"></i>次</span> 
							<span style="color:rgb(1, 211, 204)" class="energy"><i v-text="list.score"></i>g</span>
						</div> --> 

						<div class="list-content" v-if="$index == 0"> 
							<span class="rankNum" style="color:rgba(65,176,239,1);" v-text="list.ranking"></span>
							<span class="nickName schoolName" style="color:rgba(65,176,239,1);" v-text="list.school_name | noNickName"></span> 
							<span class="times" style="color:rgba(65,176,239,1);">打卡<i v-text="list.count"></i>次</span>
							<span class="energy" style="color:rgba(65,176,239,1);"><i v-text="list.score | myEnergy"></i></span>
						</div> 
						<div class="list-content" v-if="$index != 0"> 
							<span v-bind:class="{ 'rankToper': $index == 1|| $index == 2 || $index == 3}" class="rankNum" v-text="list.ranking"></span>
							<span v-bind:class="{ 'rankToper': $index == 1|| $index == 2 || $index == 3}" class="nickName schoolName" v-text="list.school_name | noNickName"></span> 
							<span v-bind:class="{ 'rankToper': $index == 1|| $index == 2 || $index == 3}" class="times">打卡<i v-text="list.count"></i>次</span>
							<span v-bind:class="{ 'rankToper': $index == 1|| $index == 2 || $index == 3}" class="energy"><i v-text="list.score | myEnergy"></i></span>
						</div>
					</div>
					<div class="rank-foot">
						<p style="text-align:center;font-size:.8rem;line-height:30px;"><!-- <span v-text="public_name"></span> -->全国已有<span v-text="schoolCount"></span>个高校自媒体加入早起计划</p> 
					</div>
				</div> 
				<!-- 全校榜单排行--> 
				<div class="rank-body" id="allLists" v-show="tab_c">
					<div class="rank-list" v-for="list in data.lists">
						<div class="list-content" v-if="$index == 0 && list.ranking == 0">
							<span style="color:rgb(1, 211, 204)" class="rankNum">无</span>
							<span class="headImg"> 
								<div class="vip_icon" v-bind:class="{'vip_0':list.vip == 0,'vip_1':list.vip == 1,'vip_2':list.vip == 2,'vip_3':list.vip == 3,'vip_4':list.vip == 4,'vip_5':list.vip == 5}"></div><img :src="list.headimgurl | noHeadImg" alt="" width="30" height="auto">
							</span> 
							<span class="nickName" v-bind:class="{ 'vip_color_0':list.vip == 0,'vip_color_1':list.vip == 1,'vip_color_2':list.vip == 2,'vip_color_3':list.vip == 3,'vip_color_4':list.vip == 4,'vip_color_5':list.vip == 5}" v-text="list.nickname | noNickName"></span>
							<!-- <span style="color:rgb(1, 211, 204)" class="nickName" v-text="list.nickname | noNickName"></span> -->
							<span style="color:rgb(1, 211, 204)" class="times" style="font-size:1rem;">未上此榜</span>
							<!-- <span class="energy"><i v-text="list.score"></i>g</span> -->
						</div>
						<div class="list-content" v-if="$index == 0 && list.ranking != 0"> 
							<span style="color:rgb(1, 211, 204)" class="rankNum" v-text="list.ranking"></span>
							<span class="headImg">
								<div class="vip_icon" v-bind:class="{'vip_0':list.vip == 0,'vip_1':list.vip == 1,'vip_2':list.vip == 2,'vip_3':list.vip == 3,'vip_4':list.vip == 4,'vip_5':list.vip == 5}"></div><img :src="list.headimgurl | noHeadImg" alt="" width="30" height="auto">
							</span> 
							<span class="nickName"  v-bind:class="{ 'vip_color_0':list.vip == 0,'vip_color_1':list.vip == 1,'vip_color_2':list.vip == 2,'vip_color_3':list.vip == 3,'vip_color_4':list.vip == 4,'vip_color_5':list.vip == 5}" v-text="list.nickname | noNickName"></span>
							<!-- <span style="color:rgb(1, 211, 204)" class="nickName" v-text="list.nickname | noNickName"></span> -->
							<span style="color:rgb(1, 211, 204)" class="times">打卡<i v-text="list.count"></i>次</span>
							<span class="energy" v-bind:class="{ 'vip_color_0':list.vip == 0,'vip_color_1':list.vip == 1,'vip_color_2':list.vip == 2,'vip_color_3':list.vip == 3,'vip_color_4':list.vip == 4,'vip_color_5':list.vip == 5}"><i v-text="list.score | myEnergy"></i></span>
						</div>
						<div class="list-content" v-if="$index != 0">
							<span v-bind:class="{ 'rankToper': $index == 1|| $index == 2 || $index == 3}" class="rankNum" v-text="list.ranking"></span>
							<span class="headImg">
								<div class="vip_icon" v-bind:class="{'vip_0':list.vip == 0,'vip_1':list.vip == 1,'vip_2':list.vip == 2,'vip_3':list.vip == 3,'vip_4':list.vip == 4,'vip_5':list.vip == 5}"></div><img :src="list.headimgurl | noHeadImg" alt="" width="30" height="auto">
							</span> 
							<span class="nickName"  v-bind:class="{ 'vip_color_0':list.vip == 0,'vip_color_1':list.vip == 1,'vip_color_2':list.vip == 2,'vip_color_3':list.vip == 3,'vip_color_4':list.vip == 4,'vip_color_5':list.vip == 5}" v-text="list.nickname | noNickName"></span>
							<span v-bind:class="{ 'rankToper': $index == 1|| $index == 2 || $index == 3}" class="times">打卡<i v-text="list.count"></i>次</span>
							<span  class="energy" v-bind:class="{ 'vip_color_0':list.vip == 0,'vip_color_1':list.vip == 1,'vip_color_2':list.vip == 2,'vip_color_3':list.vip == 3,'vip_color_4':list.vip == 4,'vip_color_5':list.vip == 5}"><i v-text="list.score | myEnergy"></i></span>
						</div>
					</div>
					<div class="rank-foot">
						<p style="text-align:center;font-size:.8rem;line-height:30px;"><!-- <span v-text="public_name"></span> -->已有<span v-text="totalCount"></span>位小伙伴在此打卡</p>
					</div>
				</div> 
			</div>  
			<!-- 榜单排行 end--> 
		</div> 
		<footer> 
			<!-- 此处注释为版权区动画触发器 勿删-->
			<!-- <div class="hide">
				<span class="showPocket" @click="footerTouch">&nbsp;</span>
			</div> -->
			<div class="copyRight">
				<p><span class="wxLogo">&nbsp;</span><span class="publicName" v-text="public_name"></span></p>
				<p>关注<span class="publicName">口袋高校助手</span>接收早起提醒</p>
			</div> 
		</footer> 
		<!-- 获取签到暗号 start-->
		<div id="dialog">
			<div class="js_dialog classHide" id="getKeywords" v-bind:class="{ 'classShow': state == 1 && ifErrcode == 0}">
			    <div class="weui-mask"></div>
			    <div class="weui-dialog" id="stateTwo" v-bind:class="{ 'classShow': state == 1 && ifErrcode == 0}">
			        <div class="weui-dialog__hd"><strong class="weui-dialog__title" style="color:rgba(39,204,127,1);font-size:1.5rem;">获得口令</strong></div>
			        <div class="weui-dialog__bd"> 
			        	<p>长按,在公众号回复口令打卡</p>
			        	<img :src="'http://open.weixin.qq.com/qr/code/?username=' + media_id" alt="早起打卡" width="180" height="180">
			        	<h3><span style="margin-top:0px;" v-text="myKey"></span></h3>
			        </div>
			        <p class="keyInfo">
			        	框内文本均为打卡口令，复制即可
			        	<!--  长按以上代码可复制签到口令 -->
			        </p>
			        <div class="giveUp">  
			            <a @click="showCancle" class="weui-dialog__btn weui-dialog__btn_default">隐 藏</a> 
			        </div> 
			    </div>
			</div>
			<!-- 成就卡蒙板 -->
			<div class="js_dialog" id="iosDialog2" v-show="state == 2" style="opacity:1;">
			    <div class="weui-mask"></div>
			    <div class="sharePoint" v-if="sharePoint">
			    	<img src="http://static.pocketuniversity.cn/kdgx/EveryDay/1.0/images/wxShare.png" alt="" width="40" height="auto">
			    	<p>点击右上角，分享给朋友</p>
			    </div>  
			    <div class="weui-dialog" style="width:280px;box-shadow:0 0 8px rgba(255,255,255,.8);border-radius:5px;">
			        <div class="myCard"> 
			        	<div class="cardHeadImg"> 
			        		<!-- <div class="vip_icon"></div> -->
			        		<img :src="cardMessage.headimgurl| noHeadImg" alt="" width="50" height="50">
			        	</div>
			        	<div class="justShare" @click="cardCancle">
			        		<span>隐藏</span>
			        		<!-- <img src="http://static.pocketuniversity.cn/kdgx/EveryDay/1.0/images/share.png" alt="" width="20" height="auto"> -->
			        	</div> 
			        	<div class="cardContent"> 
			        		<div class="tabBox"> 
			        			<span>起床时间</span>
			        			<span v-if="cardMessage.timestamp" v-text="cardMessage.timestamp | timer"></span>
			        			<span v-if="!cardMessage.timestamp">尚未打卡</span> 
			        		</div>
			        		<div class="tabBox">
			        			<span>连续早起天数</span>
			        			<span v-if="cardMessage.count" v-text="cardMessage.count"></span>
			        			<span v-if="!cardMessage.count">--</span>
			        		</div>
			        		<div class="tabBox">
			        			<span>今日排名</span> 
			        			<span v-if="cardMessage.count" v-text="cardMessage.ranking"></span>
			        			<span v-if="!cardMessage.count">--</span>
			        		</div>
			        	</div>
			        	<div @click="wxShare" class="hideBtn">
			        		<span>分享成就卡</span>
			        	</div> 
			        </div>
			    </div> 
			</div> 
			<!-- 数据加载动画预留 -->
			<div id="loadingToast" style="display:none;">
			    <div class="weui-mask_transparent"></div>
			    <div class="weui-toast">
			        <i class="weui-loading weui-icon_toast"></i>
			        <p class="weui-toast__content">数据加载中</p>
			    </div>
			</div>
		</div>
		<!-- 获取签到暗号 end-->
	  <!-- 应用启动动画 end-->
	  </div>
    </div>  
    <!-- 应用脚本依赖 start-->
    <script type="text/javascript" src="http://tajs.qq.com/stats?sId=59591163" charset="UTF-8"></script>
    <script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
    <script src="http://cdn.bootcss.com/jquery/2.1.4/jquery.min.js"></script>
    <!--<script src="http://static.pocketuniversity.cn/kdgx/EveryDay/1.0/js/lib/jquery.min.js"></script>-->
    <script src="http://static.pocketuniversity.cn/kdgx/EveryDay/1.0/js/lib/vue.min.js"></script>
    <script src="http://static.pocketuniversity.cn/kdgx/EveryDay/1.0/js/main.js"></script>
    <!-- 应用脚本依赖 end-->
  </body>
</html>