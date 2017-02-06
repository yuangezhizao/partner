<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>弹幕管理</title>
    <style>
    	html, body {
    		width: 100%;
    		height: 100%;
    		padding: 0;
    		margin: 0;
    		box-sizing: border-box;
    		overflow: hidden;
    	}
    	#app {
    		width: 100%;
    		height: 100%;
    		overflow: auto;
    		overflow-y: scroll;
    		box-sizing:border-box;
    		padding-bottom: 30px;
    	}
	    table {
	    	width: 100%;
	    	border-collapse:collapse;
	    }
        td{
			text-align: center;
		    font-size: 14px;
		    padding: 15px;
        }
        .opera-btn {
    	    height: 25px;
		    padding: 0 10px;
		    margin: 0 10px;
		    background-color: #efefef;
		    border: 1px solid #cac9c9;
		    border-radius: 3px;
            cursor: pointer;
        }
        .input-box {
        	margin: 0 10px;
        }
        .enter-text {
    	    width: 300px;
        }
        .header-tr {
        	padding: 10px 0 ;
        }
        .border-tr{
        	border:1px solid rgba(53,53,53,.2);
        	padding:10px;
        }
        .search-table {
        	margin: 20px 0;
        }
        .search-wrapper {
        	display: flex;
        	justify-content: center;
        	padding: 20px 0;
        }
        .search {
        	/*width: 400px;*/
        	height: 20px;
        }
	
		/*没有弹幕*/
		.no-list {
			width: 100%;
			padding: 10px;
			font-size: 16px;
			text-align: center;
		}
		/*没有弹幕*/

        /*applink*/
    	.applink {
    		width: 100%;
    		position: absolute;
    		bottom: 0;
    		left: 0;
    		background-color: white;
    		margin: 0;
    	}
    	.applink span {
    			font-size: 12px;
    		}
		.applink input {
			width: 50%;
		}
        /*applink*/

        /*删除成功*/
		.delete-success {
			padding: 10px 20px;
			color: white;
			border-radius: 6px;
			position: fixed;

			top: 0;
			left: 50%;
			transform:translateX(-50%);
			background-color: rgba(53,53,53,.8)
		}
        /*删除成功*/
        .pointer {
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div id='app'>
    	<!-- 删除成功  -->
		<p class="delete-success" style="display:none">
			<span>删除成功</span>
		</p>
    	<!-- 删除成功  -->
   		 <!-- 顶部搜索，设置 -->
    	<div class="search-wrapper">
    		<div class="input-box">
	    		<input class="search" v-model="content" type="text" placeholder="搜索弹幕">
	    		<button class="opera-btn" @click="search">搜索弹幕</button>
    		</div>
    		<div class="input-box">
    			<input class="search enter-text" v-model="enterText" id="changeEnterText" type="text" maxlength="10" placeholder="修改广场入口按钮文字">
    			<button class="opera-btn" @click="changeEnterText">确认修改</button>
    		</div>

             <button class="opera-btn" @click="closeApp">点此关闭广场</button>
    	</div>
    	<!-- 顶部搜索，设置 -->

    	<!-- 搜索弹幕 -->
		<table class="search-table" v-show="showsearch" border="0" cellspacing="0" cellpadding="0">
            <tbody>
                <tr class="header-tr">
                    <td>搜索结果</td>
                    <td>操作</td>
                </tr>
                <tr v-for="item in searchList" class="border-tr">
                    <td>{{item.content}}</td>
                    <td style="padding: 5px;">
                        <button class="opera-btn" @click="delete" data-type="search" data-id="{{item.comment_id}}" :data-index="$index">删除</button>
                    </td>
                </tr>
            </tbody>
        </table>
        <!-- 搜索弹幕 -->

        <!-- 所有弹幕 -->
        <table v-show="!nolist" border="0" cellspacing="0" cellpadding="0">
            <tbody>
                <tr class="header-tr">
                    <td>弹幕内容</td>
                    <td>操作</td>
                </tr>
                <tr v-for="item in list" class="border-tr">
                    <td>{{item.content}}</td>
                    <td style="padding: 5px;">
                        <button class="opera-btn" @click="delete" data-id="{{item.comment_id}}" :data-index="$index">删除</button>
                    </td>
                </tr>
            </tbody>
        </table>
        <!-- 所有弹幕 -->

        <!-- 没有弹幕 -->
        <p class="no-list" v-show="noMore">
        	没有更多
        </p>
        <!-- 没有弹幕 -->

        <!-- 没有弹幕 -->
        <p class="no-list" v-show="nolist">
        	你的卖室友应用里面还没有人发过弹幕哦~
        </p>
        <!-- 没有弹幕 -->

        <!-- 应用入口 -->
         <p class="applink">
	          <span style="color:#E7254F">买卖广场内容来源于你的标记，此链接触发买卖广场应用</span>
	          <input type="input" readonly="readonly" value="http://www.pocketuniversity.cn/partner.php?glz={{mediaId}}&history=1">
          </p>
        <!-- 应用入口 -->
    </div>
    <script type="text/javascript" src="http://tajs.qq.com/stats?sId=59591163" charset="UTF-8"></script>
<script src="http://static.pocketuniversity.cn/kdgx/lib/utils/utils.js"></script>
<script src="http://static.pocketuniversity.cn/kdgx/barragemanage/vue.js"></script>
<script>
    var vm = new Vue({
        el:'#app',
        data: function () {
            return {
            	mediaId: '',
                list: [],
                searchList:[],
                page: 1,
                loading: false,
                noMore: false,
                showsearch: false,
                content: '',
                enterText: '',
                nolist: false,
                noMore: false
            }
        },
        ready: function () {
        	this.mediaId = parent.window.mediaId
            this.getList()
        },
        methods: {
            getList: function () {
            	if(this.loading || this.noMore || this.nolist) {
            		return
            	}
                var _this = this
                this.loading = true
                _kd.mAjax({
                	method: 'POST',
                	url: 'http://www.pocketuniversity.cn/index.php/partner/acSquare/getCommentToAdmin?page=' + _this.page,
                	data: {
                		'media_id':this.mediaId,
                		'from': 1
                	},
                	success: function (response) {
                		_this.loading = false
                    	var res = JSON.parse(response)
                    	if(res.length == 0) {
                    		if(_this.page == 1) {
                    			_this.nolist = true
                    			return
                    		}else {
                    			_this.noMore = true
                    			return
                    		}	
                    	}
                    	res.forEach(function (item) {
                    		_this.list.push(item)
                    	})
                        
                        _this.page ++
                	}
                })
            },
            delete: function (e) {
                var _this = this
                var xhr = new XMLHttpRequest()

                var id = e.currentTarget.dataset.id,
                	index = e.currentTarget.dataset.index,
                	type = e.currentTarget.dataset.type
                xhr.onload = function () {
                    if(xhr.readyState == 4 && xhr.status == 200) {
                    	if(type=='search') {
                    		_this.searchList.splice(index, 1)
                    	}else {
                    		_this.list.splice(index, 1)
                    	}

                        // alert(JSON.parse(xhr.responseText).msg)
                        _kd.$('.delete-success').show()
                        setTimeout(function () {
                        	_kd.$('.delete-success').hide()
                        }, 1000)
                    }
                }

                xhr.open('POST', 'http://www.pocketuniversity.cn/index.php/partner/acSquare/deleteComment')
                xhr.setRequestHeader("Content-Type","application/x-www-form-urlencoded;")
                xhr.send('comment_id=' + id)
            },
            search: function () {
            	var _this = this
                if(this.content.trim() == '') {
                	 alert('请输入你要搜索的弹幕内容')
                	_this.showsearch = false
                	return
                }
                _kd.mAjax({
                	method: 'POST',
                	url: 'http://www.pocketuniversity.cn/index.php/partner/acSquare/getCommentToAdmin?page=1',
                	data: {
                		media_id: this.mediaId,
                		from: 1,
                		content: this.content
                	},
                	success: function (response) {
                    	if(JSON.parse(response).length == 0) {
                    		return alert('没有找到相关弹幕')
                    		_this.showsearch = false
                    	}
                		_this.searchList = JSON.parse(response)

                    	_this.showsearch = true
                	}
                })
            },
            changeEnterText: function () {
            	var _this = this
            	// console.log(this.mediaId,this.enterText)
            	// return
            	if(this.enterText.length > 10) {
            		return alert('最多10个字')
            	}
            	if(this.enterText.trim() == '') {
            		return alert('不能为空哦~')
            	}
            	_kd.mAjax({
            		method: 'POST',
            		url: '/index.php/partner/partner/updateDsgText',
            		data: {
            			media_id: this.mediaId,
            			dsg_text: this.enterText.trim()
            		},
            		success: function (response) {
            			var res = JSON.parse(response)
            			// if(res.error == 0 ) {
            				alert(res.msg)
            			// }
            		}
            	})
            },
            closeApp: function () {
              var _this = this
              _kd.mAjax({
                  method: 'POST',
                  url: '/index.php/partner/partner/updateDsgSquare',
                  data: {
                      media_id: this.mediaId,
                      acsquare: '0'
                  },
                  success: function (response) {
                      var res = JSON.parse(response)
                      if(res.error == 0) {
                          _this.isOpen = true
                          alert('关闭广场成功')
                          parent.window.closeSquareFromParent()

                          return
                      }
                      // alert('开启失败')
                  }
              })
            }
        }
    })

	var app = document.querySelector('#app')
	app.onscroll = function () {
		if(app.scrollHeight - app.scrollTop < (app.offsetHeight+300)) {
			vm.getList()
		}
	}
</script>
</body>
</html>