接口列表
主机ip:
	10.10.10.108
	192.168.2.141  
端口号：8080

默认都是带tk      不带tk的会标注

登录  get  index/user/login

节点图      get    index/user/node
                    us_account 或不传
					
注册用户    post   index/user/add
				  节点人账号:a_acc
				  推荐人账号:p_acc
				  左右区：us_qu
				  手机号：us_tel
				  账户名：us_account
				  真实姓名：us_real_name
				  登录密码：us_pwd
				  安全密码：us_safe_pwd

				  
一条新闻    get   index/total/news  不带tk
用户反馈    post  index/user/relation
				 内容    me_content
修改密码    post    index/user/pass
                   新密码 us_pwd
				   验证码 sode
				   手机号  us_tel 
转账电子币   post   index/profit/trans
				对方账号   tr_account  
				数额  tr_num 
				手机号  us_tel
				验证码    sode
转电子币列表 get   index/profit/trans	
				带tk

短信验证码 post  index/user/send
				us_tel 手机号

将积分转换成电子币  post   index/profit/convert
					us_tel  手机号
					sode     验证码
					convert_num  数量
转换记录    get   index/profit/convert
积分明细    get   index/profit/msc


上传图片   post  index/total/uploads   不带token
       				唯一base64字符串  img
信息修改   post    index/user/edit
			头像  us_head_pic
			昵称改成真实姓名吧    us_real_name
			手机号：us_tel
			支付宝：us_alipay
			微信：us_wechat
忘记密码   post  index/total/fg
			us_tel
			us_pwd
			sode
没对过：

用户自己信息  get   index/user/login
其它用户信息  get   index/user/info     id或者us_tel
  
修改安全密码  post  index/user/safe
                   新密码 us_safe_pwd
					验证码 sode
				   手机号  us_tel
团队        get    index/user/team
                    账户名:us_account或手机号us_tel或真实姓名us_real_name
激活用户    post    index/user/active
			地址 us_addr_addr
			收货人  us_addr_person
			收货电话  us_addr_tel
短信验证码
		  	注册：  post      index/total/send
		  			手机号：   us_tel
					类型：  type 固定字符串  reg
			忘记密码验证码  post     index/total/send
					手机号：	us_tel
					类型    type 固定字符串 fg
电子币记录 get  index/profit/wal
 
提现  post  index/profit/tx
			手机号 us_tel
			验证码 sode
			银行卡号   tx_account
			银行名称   tx_addr
			收款人     tx_name
提现记录



分类列表  post   index/shop/cate
产品列表  post   index/shop/prod
			分类id  cate_id
			猜你喜欢  is_like
			推荐	  is_hot
产品详情  post   index/shop/prodDetail
			产品 id
商家列表  post   index/shop/mer
商家详情  post   index/shop/merDetail
			商家 id


购物车列表  post    index/cart/cart

添加地址    post    index/addr/add
				收货人  addr_name
                收货电话  addr_tel
                收货地址  addr_stree
地址列表  post  index/addr/index

地址详情   post   index/addr/xq
				地址id   id
				不传id  就是默认地址  没有默认地址就是第一个  没有第一个就是空
地址修改  post  index/addr/edit
			id    id
			收货人  addr_name
			收货电话  addr_tel
			收货地址  addr_stree
地址设置默认 post  index/addr/def
			地址id   id
地址删除  post   index/addr/del
			地址id   id

添加订单  post   index/order/add
			支付密码   us_safe_pwd
            商品id    prod_id
            商品数量   prod_num
            地址id    addr_id
            备注信息   order_note

订单列表  post  index/order/index
			状态  detail_status
				待发货 1
				待收货 2
				已完成 3
订单详情  post   index/order/detail
			订单id   id
添加到购物车 post index/cart/add
                产品id  id		
购物车列表   post  index/cart/cart

修改购物车数量  post index/cart/num
				购物车id   id
				数量   num
删除购物车某个商品  post  index/cart/del
				购物车id   id

添加订单  post   index/order/card_add
		 *  地址id   addr_id
		*  备注  order_note
		*  支付密码  us_safe_pwd
		*  购物车id组 arrid



