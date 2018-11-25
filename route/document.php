<?php
/**
 * @SWG\Swagger(
 *   schemes={"http"},
 *   host="192.168.2.182:1235",
 *   consumes={"multipart/form-data"},
 *   produces={"application/json"},
 *   @SWG\Info(
 *     version="2.3",
 *     title="豫味鲜",
 *     description="接口文档 参考<br>"
 *   ),
 *   @SWG\Tag(
 *     name="Store",
 *     description="商城",
 *   ),
 *	 @SWG\Tag(
 *     name="Login",
 *     description="登陆",
 *   ),
 *   @SWG\Tag(
 *     name="User",
 *     description="用户",
 *   ),
 *
 *   @SWG\Tag(
 *     name="Every",
 *     description="公共",
 *   ),

 *   @SWG\Tag(
 *     name="Order",
 *     description="订单",
 *   ),
 * )
 */

/**
 * @SWG\Get(
 *   path="/user/detail",
 *   tags={"User"},
 *   summary="用户详情 传入id 则表示某个人的详情",
 *   @SWG\Parameter(name="id", type="string",  in="query",description="输入id 则为查寻的该id人的详情"),
 *   @SWG\Response(
 *     response=200,
 *     description="成功"
 *   ),
 * )
 */
/**
 * @SWG\Get(
 *   path="/user/direct",
 *   tags={"User"},
 *   summary="用户下级 传入id 则表示某个人的下级",
 *   @SWG\Parameter(name="id", type="string",  in="query",description="输入id 则为查寻的该id人的详情"),
 *   @SWG\Response(
 *     response=200,
 *     description="成功"
 *   ),
 * )
 * @SWG\Post(
 *   path="/user/edit",
 *   tags={"User"},
 *   summary="修改信息",
 *   @SWG\Response(
 *     response=200,
 *     description="成功"
 *   ),
 * )
 * @SWG\Post(
 *   path="/user/tixian",
 *   tags={"User"},
 *   summary="添加提现",
 *   @SWG\Response(
 *     response=200,
 *     description="成功"
 *   ),
 * )
 * @SWG\Post(
 *   path="/user/ti_record",
 *   tags={"User"},
 *   summary="提现记录",
 *   @SWG\Response(
 *     response=200,
 *     description="成功"
 *   ),
 * )
 * @SWG\Post(
 *   path="/user/msc_record",
 *   tags={"User"},
 *   summary="佣金记录",
 *   @SWG\Response(
 *     response=200,
 *     description="成功"
 *   ),
 * )
 * @SWG\Post(
 *   path="/user/wallet_record",
 *   tags={"User"},
 *   summary="购物币记录",
 *   @SWG\Response(
 *     response=200,
 *     description="成功"
 *   ),
 * )
 * @SWG\Post(
 *   path="/user/addr_add",
 *   tags={"User"},
 *   summary="添加地址",
 *   @SWG\Response(
 *     response=200,
 *     description="成功"
 *   ),
 * )
 * @SWG\Get(
 *   path="/user/addr_edit",
 *   tags={"User"},
 *   summary="详细信息",
 *   @SWG\Response(
 *     response=200,
 *     description="成功"
 *   ),
 * )
 * @SWG\Post(
 *   path="/user/addr/edit",
 *   tags={"User"},
 *   summary="修改地址",
 *   @SWG\Response(
 *     response=200,
 *     description="成功"
 *   ),
 * )
 * @SWG\Get(
 *   path="/user/addr_list",
 *   tags={"User"},
 *   summary="地址列表",
 *   @SWG\Response(
 *     response=200,
 *     description="成功"
 *   ),
 * )
 */
/**
 * @SWG\Get(
 *   path="/store/index",
 *   tags={"Store"},
 *   summary="门店列表",
 *   @SWG\Response(
 *     response=200,
 *     description="成功"
 *   ),
 * )
 * @SWG\Get(
 *   path="/store/hot",
 *   tags={"Store"},
 *   summary="热销商品",
 *   @SWG\Response(
 *     response=200,
 *     description="成功"
 *   ),
 * )

 * @SWG\Get(
 *   path="/store/product",
 *   tags={"Store"},
 *   summary="门店详情 分类产品列表",
 *   @SWG\Response(
 *     response=200,
 *     description="成功"
 *   ),
 * )

 * @SWG\Get(
 *   path="/store/detail",
 *   tags={"Store"},
 *   summary="产品详情",
 *   @SWG\Response(
 *     response=200,
 *     description="成功"
 *   ),
 * )
 */

/**
 * @SWG\Get(
 *   path="/login/login",
 *   tags={"Login"},
 *   summary="会员登陆",
 *   @SWG\Response(
 *     response=200,
 *     description="成功"
 *   ),
 * )
 * @SWG\Post(
 *   path="/login/Register",
 *   tags={"Login"},
 *   summary="会员注册",
 *   @SWG\Response(
 *     response=200,
 *     description="成功"
 *   ),
 * )
 * @SWG\Post(
 *   path="/login/forget",
 *   tags={"Login"},
 *   summary="忘记密码",
 *   @SWG\Response(
 *     response=200,
 *     description="成功"
 *   ),
 * )
 * @SWG\Post(
 *   path="/every/send",
 *   tags={"Every"},
 *   summary="验证码",
 *   @SWG\Response(
 *     response=200,
 *     description="成功"
 *   ),
 * )
 * @SWG\Get(
 *   path="/every/config",
 *   tags={"Every"},
 *   summary="系统设置",
 *   @SWG\Response(
 *     response=200,
 *     description="成功"
 *   ),
 * )
 * @SWG\Post(
 *   path="/every/alldel",
 *   tags={"Every"},
 *   summary="删除记录",
 *   @SWG\Response(
 *     response=200,
 *     description="成功"
 *   ),
 * )
 * @SWG\Post(
 *   path="/every/sctp",
 *   tags={"Every"},
 *   summary="上传图片",
 *   @SWG\Response(
 *     response=200,
 *     description="成功"
 *   ),
 * )
 */
/**
 * @SWG\Post(
 *   path="/order/add",
 *   tags={"Order"},
 *   summary="添加订单",
 *   @SWG\Response(
 *     response=200,
 *     description="成功"
 *   ),
 * )
 * @SWG\Post(
 *   path="/order/cart",
 *   tags={"Order"},
 *   summary="第一次添加订单",
 *   @SWG\Response(
 *     response=200,
 *     description="成功"
 *   ),
 * )
 * @SWG\Get(
 *   path="/order/getcart",
 *   tags={"Order"},
 *   summary="获取上面订单",
 *   @SWG\Response(
 *     response=200,
 *     description="成功"
 *   ),
 * )
 * @SWG\Get(
 *   path="/order/index",
 *   tags={"Order"},
 *   summary="订单列表",
 *   @SWG\Response(
 *     response=200,
 *     description="成功"
 *   ),
 * )
 */