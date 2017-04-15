<?php
set_time_limit(2); //请设置该值1或2
include_once("51SDK/appinclude.php");
include_once("dbinc.php");
try {

    $order_id = $OpenApp_51->fb_params['order_id'];
    $order_price = $OpenApp_51->fb_params['order_price'];
    $order_num = $OpenApp_51->fb_params['order_num'];
    $pay_user = $OpenApp_51->fb_params['user'];
    $pay_environment = $OpenApp_51->fb_params['environment'];
    
    $real_order_id=intval(substr($order_id,2));
    $origin_user = sql_fetch_one_cell("select passport from log_51_charge where id='$real_order_id'");
    if ( !preg_match("/^\d+$/", $order_id) || !is_numeric($order_price) || intval($order_num)<1 || empty($pay_user)
        || $pay_user!=$origin_user ) {
        throw new Exception('非法数据');
    }
    if ( $pay_environment!='production' ) {
        throw new Exception('测试支付');
    }

    /* 验证订单，发货  */
    //TODO (APP发货代码)

    /* 签名发货结果 */
    //order_code=1成功,order_code=0失败
    $params = array('order_code'=>1, 'order_id'=>$order_id, 'order_price'=>$order_price, 'order_num'=>$order_num);
    $OpenApp_51->api_client->set_encoding("GBK");
    $res = $OpenApp_51->api_client->create_post_string("", $params);

    /* 返回发货结果(直接把签名后的字串输出到页面即可) */
    echo $res;
    exit;

} catch (Exception $e) {
    die($e->getMessage());
}
?>


