<?php

/**
 * @name Component_Base_Qta
 * @desc qta dao，用于获取问题属性
 * @author  程童(chengtong@baidu.com)
 */
class Service_Data_Qta
{
    //本服务名
    const SERVICE_NAME = 'qta';
    //调用者名
    public static $reqsvr = '';

    //定义qta_req_type请求类型, 0~2为qta 1.0接口(已废弃)，3~4为qta 2.0接口
    const REQ_QB = 3;
    const REQ_QB_GROUP = 4;

    //回答的排序方式
    const QTA_ORDER_TYPE_ANY = 0;    //任意次序
    const QTA_ORDER_TYPE_TIME = 1;    //按回复时间排序（最佳，推荐，投票答案置首位）
    const QTA_ORDER_TYPE_RANK = 2;    //按rank值、回复时间排序
    const QTA_ORDER_TYPE_CLUSTER = 3;    //按好评+回复长度排序最佳答案，普通回答按时间排序
    const QTA_ORDER_TYPE_PARTITION_RANK = 4;    //rank值+分区展现+回复时间排序
    const QTA_ORDER_TYPE_CT_HIDDEN_RANK1 = 5;    //回复时间升序+分区隐藏
    const QTA_ORDER_TYPE_CT_HIDDEN_RANK2 = 6;    //回复时间降序+分区隐藏
    const QTA_ORDER_TYPE_DELETED_RANK2 = 7;    //回复时间降序+已删除回复

    //每页展示回答数
    const RES_NUM = 25;


    /**
     * 构造函数
     * @param $reqsvr 调用者
     */
    public function __construct($reqsvr = '')
    {
        self::$reqsvr = ($reqsvr == '') ? MAIN_APP : $reqsvr;
    }

    /**
     * 请求QTA接口的封装，兼容新老接口
     * @param  array $arrReqQid qid的数组，2.0时是{{'qid'=>, 'rid'=>, 'uid'=>},...}格式的数组，uid可选
     * @param  array $arrQatr 请求问题属性的数组
     * @param  integer $intReqType 请求类型，0~2为QTA1.0的接口(已废弃，只使用2.0接口)，3~4为qta2.0的接口
     * @param  array $arrRatr 请求回复属性的数组
     * @param  integer $intOrderType 指定排序类型
     * @param  integer $intPn 分页的起始位置
     * @param  integer $intRn 每页展示数量
     * @return array                    结果类型参见qta接口文档
     */
    private function _get($arrReqQid, $arrQatr, $intReqType, $arrRatr = array(), $intOrderType = 0, $intPn = 0, $intRn = self::RES_NUM)
    {
        //主要参数的校验
        $intReqType = intval($intReqType);
        $intOrderType = intval($intOrderType);
        if (empty($arrReqQid) || ($intReqType != self::REQ_QB && $intReqType != self::REQ_QB_GROUP)) {
            return false;
        }
        if ($intOrderType < self::QTA_ORDER_TYPE_ANY || $intOrderType > self::QTA_ORDER_TYPE_DELETED_RANK2) {
            if ($intReqType === self::REQ_QB_GROUP) {
                $intOrderType = self::QTA_ORDER_TYPE_CLUSTER;
            } else {
                $intOrderType = self::QTA_ORDER_TYPE_PARTITION_RANK;
            }
        }

        $rpack = array();
        $rpack['module_name'] = MAIN_APP;
        $rpack['qta_req_type'] = $intReqType;

        //判断是否是新接口
        foreach ($arrReqQid as $key => $value) {
            $qid = isset($value['qid']) ? $value['qid'] : 0;
            if ($qid != 0) {
                $rid = isset($value['rid']) ? $value['rid'] : 0;
                $uid = isset($value['uid']) ? $value['uid'] : 0;
                $rpack['req_qid'][] = $qid . ':' . $rid . ':' . $uid;
            }
        }
        //请求属性默认携带qid加密串
        $arrQatr[] = 'encode_qid';
        $rpack['req_atr'] = array(
            'qatr' => $arrQatr,
            'ratr' => $arrRatr,
            'rnum' => array(
                'order_type' => $intOrderType,
                'pn' => intval($intPn),
                'rn' => intval($intRn),
            ),
        );
        $rpack['req_atr_num'] = 0;
        $rpack['req_qid_num'] = count($rpack['req_qid']);

        //调用QTA服务
        ral_set_pathinfo('');
        ral_set_querystring('');
        ral_set_header('', RAL_SET_HEADER_RESET);
        $res = ral('Qta', MAIN_APP, $rpack, rand());
        if (empty($res)) {
            Bd_Log::fatal('[Component_Base_Qta] Error:[Qta], Abstract:[connect error], Detail:[talk with Qta service failed, error_no: ' . ral_get_errno() . ']', self::SERVICE_NAME, self::$reqsvr);
            return false;
        }
        //数据错误
        if ($res['error_no'] != 0 && $res['error_no'] != 1) {
            Bd_Log::fatal('[Component_Base_Qta] Error:[Qta], Abstract:[data error], Detail:[qta res data invalid, error_no: ' . $res['error_no'] . ']', self::SERVICE_NAME, self::$reqsvr);
            return false;
        }

        //补写QTA返回结果的排序类型
        if (!empty($res['qta_data'])) {
            foreach ($res['qta_data'] as $key => $value) {
                $res['qta_data'][$key]['sort_type'] = $intOrderType;
            }
        }
        return $res['qta_data'];
    }

    /**
     * 请求归一化问题的所有属性，支持回答分页，每页默认显示25条，回答排序方式只能为：按好评+回复长度排序最佳答案，普通回答按时间排序
     * @param  array $qids 问题列表，{{'qid'=>, 'rid'=>, 'uid'=>},...}格式的数组，qid必须，rid, uid可选(默认为0)
     * @param  integer $intPn 用于回答的分页，指定偏移量
     * @param  integer $intRn 指定每页显示的条数，默认为25条
     * @return array          返回聚簇问题和回答的所有属性
     */
    public function getQBGroup($qids, $intPn = 0, $intRn = self::RES_NUM)
    {
        return $this->_get($qids, array('atr_all'), self::REQ_QB_GROUP, array('atr_all'),
            self::QTA_ORDER_TYPE_CLUSTER, $intPn, $intRn);
    }

    /**
     * 请求单个问题的所有属性，支持回答分页，每页默认显示25条，默认回答排序方式为：rank值+分区展现+回复时间排序
     * @param  array $qids 问题列表，{{'qid'=>, 'rid'=>, 'uid'=>},...}格式的数组，qid必须，rid, uid可选(默认为0)
     * @param  integer $intPn 用于回答的分页，指定偏移量
     * @param  integer $intRn 指定每页显示的条数，默认为25条
     * @param  integer $orderType 返回结果的排序类型
     * @return [type]         返回单个问题包括回答的所有属性
     */
    public function getQBAll($qids, $intPn = 0, $intRn = self::RES_NUM, $orderType = self::QTA_ORDER_TYPE_PARTITION_RANK)
    {
        return $this->_get($qids, array('atr_all'), self::REQ_QB, array('atr_all'), $orderType, $intPn, $intRn);
    }

    /**
     * 请求单个问题指定的属性，支持回答分页，可以自定义排序方式，默认为：rank值+分区展现+回复时间排序
     * @param  array $qids 问题列表，{{'qid'=>, 'rid'=>, 'uid'=>},...}格式的数组，qid必须，rid, uid可选(默认为0)
     * @param  array $qatr 请求的问题属性，默认为请求全部属性
     * @param  array $ratr 请求的回答属性，默认为不请求回答
     * @param  integer $intPn 用于回答的分页，指定偏移量
     * @param  integer $intRn 指定每页显示的条数
     * @param  integer $orderType 回答排序的类型
     * @return [type]             返回单个问题和回答指定的属性
     */
    public function getQBAttr($qids, $qatr = array('atr_all'), $ratr = array(), $intPn = 0, $intRn = self::RES_NUM,
                              $orderType = self::QTA_ORDER_TYPE_PARTITION_RANK)
    {
        return $this->_get($qids, $qatr, self::REQ_QB, $ratr, $orderType, $intPn, $intRn);
    }

    /**
     * 解析问题的bit_pack字段
     * @param  integer $bit_pack 问题的bit_pack字段
     * @return array             解析之后的数组
     */
    public static function parseQBitpack($bit_pack)
    {
        //强制bitpack为正整数，否则返回false
        $bit_pack = intval($bit_pack);
        if ($bit_pack < 0) {
            return false;
        }

        $res = array();
        $res['auto_vote'] = $bit_pack & 0x01;
        $res['review_deleted'] = ($bit_pack >> 1) & 0x01;
        $res['admin_set_close_flag'] = ($bit_pack >> 2) & 0x01;
        $res['wap_flag'] = ($bit_pack >> 3) & 0x01;
        $res['is_pic_contained'] = ($bit_pack >> 4) & 0x01;
        $res['anonymous'] = ($bit_pack >> 5) & 0x01;
        $res['level_new'] = ($bit_pack >> 6) & 0x01;
        $res['has_excellent_answer'] = ($bit_pack >> 7) & 0x01;
        $res['released'] = ($bit_pack >> 8) & 0x01;
        $res['locked'] = ($bit_pack >> 9) & 0x01;
        $res['reply_locked'] = ($bit_pack >> 10) & 0x01;
        $res['content_rich_flag'] = ($bit_pack >> 11) & 0x01;
        $res['sup_rich_flag'] = ($bit_pack >> 12) & 0x01;
        $res['content_pic_flag'] = ($bit_pack >> 13) & 0x01;
        $res['ps_unindex_flag'] = ($bit_pack >> 14) & 0x01;
        $res['rank_flag'] = ($bit_pack >> 15) & 0x01;
        $res['high_value_flag'] = ($bit_pack >> 16) & 0x01;
        $res['mis_flag'] = ($bit_pack >> 17) & 0x01;
        $res['unsupport_flag'] = ($bit_pack >> 18) & 0x01;
        $res['sms_flag'] = ($bit_pack >> 19) & 0x01;
        $res['get_wealth_flag'] = ($bit_pack >> 20) & 0x01;
        $res['unpush_flag'] = ($bit_pack >> 21) & 0x01;
        $res['unpush_browse_flag'] = ($bit_pack >> 22) & 0x01;
        $res['is_challenge'] = ($bit_pack >> 25) & 0x01;
        $res['is_zhima'] = ($bit_pack >> 26) & 0x01;
        $res['is_tag'] = ($bit_pack >> 27) & 0x01;
        return $res;
    }

    /**
     * 解析回答的bit_pack字段
     * @param  integer $bit_pack 回答的bit_pack字段
     * @return array             解析之后的数组
     */
    public static function parseRBitpack($bit_pack)
    {
        //强制bitpack为正整数，否则返回false
        $bit_pack = intval($bit_pack);
        if ($bit_pack < 0) {
            return false;
        }

        $res = array();
        $res['is_recommend'] = $bit_pack & 0x01;
        $res['wap_flag'] = ($bit_pack >> 1) & 0x01;
        $res['is_pic_contained'] = ($bit_pack >> 2) & 0x01;
        $res['media_flag'] = ($bit_pack >> 3) & 0x01;
        $res['help_flag'] = ($bit_pack >> 4) & 0x01;
        $res['anonymous'] = ($bit_pack >> 5) & 0x01;
        $res['is_really_anonymous'] = ($bit_pack >> 6) & 0x01;
        $res['level_new'] = ($bit_pack >> 7) & 0x01;
        $res['comment_flag'] = ($bit_pack >> 8) & 0x01;
        $res['in_mis'] = ($bit_pack >> 9) & 0x01;
        $res['auto_recommend'] = ($bit_pack >> 10) & 0x01;
        $res['content_rich_flag'] = ($bit_pack >> 11) & 0x01;
        $res['prior_flag'] = ($bit_pack >> 12) & 0x01;
        $res['hidden_flag'] = ($bit_pack >> 13) & 0x01;
        $res['rec_canceled_flag'] = ($bit_pack >> 14) & 0x01;
        $res['mis_flag'] = ($bit_pack >> 15) & 0x01;
        $res['file_flag'] = ($bit_pack >> 16) & 0x01;
        $res['read_flag'] = ($bit_pack >> 17) & 0x01;
        $res['is_challenge'] = ($bit_pack >> 18) & 0x01;
        $res['ikaudio_flag'] = ($bit_pack >> 19) & 0x01;
        $res['mavin_flag'] = ($bit_pack >> 20) & 0x01;

        return $res;
    }

    /**
     * 解析追问追答的bit_pack字段
     * @param  integer $bit_pack 追问的bit_pack字段
     * @return array             解析之后的数组
     */
    public static function parseRABitpack($bit_pack)
    {
        //强制bitpack为正整数，否则返回false
        $bit_pack = intval($bit_pack);
        if ($bit_pack < 0) {
            return false;
        }

        $res = array();
        $res['media_flag'] = $bit_pack & 0x01;
        $res['content_rich_flag'] = ($bit_pack >> 1) & 0x01;
        $res['is_pic_contained'] = ($bit_pack >> 2) & 0x01;
        $res['file_flag'] = ($bit_pack >> 3) & 0x01;
        $res['read_flag'] = ($bit_pack >> 4) & 0x01;
        $res['ikaudio_flag'] = ($bit_pack >> 5) & 0x01;
        return $res;
    }

    /**
     * 解析uscore字段
     * @param  integer $uscore 回答或者提问中的uscore字段
     * @param  integer $level_new 标志位，区分uscore应该如何解析
     * @return array              解析后的结果
     */
    public static function parseUScore($uscore, $level_new)
    {
        //强制uscore为正整数，否则返回false
        $uscore = intval($uscore);
        if ($uscore < 0) {
            return false;
        }

        $res = array();
        if ($level_new == 0) {
            $res['uscore_value'] = $uscore & 0xFFFFFF;
            $res['icon_sysno'] = ($uscore >> 24) & 0x0F;
            $res['sex'] = ($uscore >> 28) & 0x03; //注意，性别是2位，共四种，想不通？你out了
        } else if ($level_new == 1) {
            $res['level'] = $uscore & 0x3F;
            $res['sublevel'] = ($uscore >> 6) & 0x1F;
            $res['good_rate'] = ($uscore >> 11) & 0x3FF;
            $res['icon_sysno'] = ($uscore >> 24) & 0x0F;
            $res['sex'] = ($uscore >> 28) & 0x03; //注意，性别是2位，共四种，想不通？你out了
        }
        return $res;
    }

    /**
     * 解析extpack申请优质相关字段
     * @param  integer $reply 回答相关qta数据
     * @return array              解析后的结果
     * @return array['applyExcType'] 0为可申请，非0对应具体不可申请原因
     */

    public static function parseExtPackForExcellent($reply, $user = null, $cid = null, $qinfo = null)
    {
        $ext_pack = isset($reply['ext_pack']) ? $reply['ext_pack'] : array();
        $content = isset($reply['content']) ? $reply['content'] : '';
        $replytime = isset($reply['c_timestamp']) ? $reply['c_timestamp'] : 0;
        $qtime = isset($qinfo['c_timestamp']) ? $qinfo['c_timestamp'] : 0;
        $anonymous = isset($reply['bit_pack']) ? (($reply['bit_pack'] >> 5) & 0x01) : 1;
        $apply_status = intval($ext_pack['apply_status']);
        $apply_num = intval($ext_pack['apply_high_num']);
        $excellent_status = isset($ext_pack['ans_excellent']) ? intval($ext_pack['ans_excellent']) : 2; //对于未设置过优质的填其他值
        $timeSpace = $replytime - $qtime;

        $ret = array();
        $ret['applyExcType'] = $apply_status;   //默认是否可申请优质状态,qb库记录1申请中，未填或0代表未申请过或被打回或被通过
        $ret['applyNum'] = $apply_num;          //申请优质的次数

        $applyConf = Bd_conf::getConf('/nik/dao/nik/applyGood');
        //首先对答案优质状态做判断
        switch ($excellent_status) {
            case 1: //1. 对于已经是优质答案的问题
                $ret['applyExcType'] = 2;       //已经是优质
                break;
            case 0: //2. 对于取消过优质答案的问题
                $ret['applyExcType'] = 3;       //不可再申请
                break;
            case -1: //3. 对于暂不推荐的问题(打回)
                $ret['applyExcType'] = 3;       //不可在申请
                break;
            default:
                break;
        }
        $applyGoodInvalidCid = explode(',', $applyConf['applyGoodInvalidCid']);
        //开放全分类（除了地区分类cid1，和资源共享分类cid1101）
        if (isset($cid) && in_array($cid, $applyGoodInvalidCid)) {
            $ret['applyExcType'] = 7;
        }

        //如果通过并且状态为可申请，则对用户信息做判断
        if (!empty($user) && $ret['applyExcType'] == 0 && intval($user['rateNum']) < intval($applyConf['applyGoodRateNum'])) {
            $ret['applyExcType'] = 4;   //用户采纳率或采纳数不满足条件
        }

        //对回答内容长度做判断
        if ($ret['applyExcType'] == 0 && !empty($content)) {
            $content = trim($content);
            $content = str_replace(array("\t", "\n", "\r", " "), "", $content);
            if (strlen($content) < intval($applyConf['applyGoodContentMinLength'])) {
                $ret['applyExcType'] = 5;
            }
        }

        //对申请次数做判断
        if ($ret['applyExcType'] == 0 && $apply_num >= intval($applyConf['applyGoodQuestionMaxApplyTime'])) {
            $ret['applyExcType'] = 6;
        }

        //对回答时间和提问时间的间隔做出判断
        if ($ret['applyExcType'] == 0 && $timeSpace <= intval($applyConf['applyGoodTimeSpace'])) {
            $ret['applyExcType'] = 8;
        }
        //对用户是否为匿名作出判断
        if ($ret['applyExcType'] == 0 && 1 == $anonymous) {
            $ret['applyExcType'] = 10;
        }
        return $ret;
    }

    /**
     * @brief qid加密接口（纯解密，适用于新产生qid的时刻）,支持批量加密
     *
     * @param array $arrReqQid :
     * array(
     *      array('qid' => 2, 'ctime' => 1118565374),
     *      ... ...
     * )
     * @return
     * 成功返回:
     * array(
     *      array('qid' => 2, 'encode_qid' => '555849674618600164'),
     *      ... ...
     * )
     * 失败返回false
     * @author zhouzhaopeng
     * @date 2013/08/26 17:45:37
     **/
    public static function encodeQid($arrReqQid)
    {
        if (empty($arrReqQid)) {
            return false;
        }

        $rpack = array();
        foreach ($arrReqQid as $value) {
            $qid = isset($value['qid']) ? $value['qid'] : 0;
            if ($qid > 0) {
                $ctime = isset($value['ctime']) ? $value['ctime'] : 0;
                if ($ctime <= 0) {
                    continue;
                }
                $rpack['req_qid'][] = $qid . ':' . $ctime . ':0';
            }
        }
        $rpack['req_qid_num'] = count($rpack['req_qid']);

        if ($rpack['req_qid_num'] == 0) {
            return false;
        }

        $rpack['module_name'] = MAIN_APP;
        $rpack['qta_req_type'] = self::QTA_ORDER_TYPE_CT_HIDDEN_RANK1;
        $rpack['req_atr'] = array(
            'qatr' => array('qid', 'encode_qid'),
            'ratr' => array(),
            'rnum' => array(
                'order_type' => 0,
                'pn' => 0,
                'rn' => 0,
            ),
        );
        $rpack['req_atr_num'] = 0;

        //调用QTA服务
        ral_set_pathinfo('');
        ral_set_querystring('');
        ral_set_header('', RAL_SET_HEADER_RESET);
        $res = ral('Qta', MAIN_APP, $rpack, rand());
        if (empty($res)) {
            Bd_Log::fatal('[Component_Base_Qta] Error:[Qta], Abstract:[connect error], Detail:[talk with Qta service failed, error_no : ' . ral_get_errno() . ']', self::SERVICE_NAME, self::$reqsvr);
            return false;
        }

        //数据错误
        if ($res['error_no'] != 0 && $res['error_no'] != 1) {
            Bd_Log::fatal('[Component_Base_Qta] Error:[Qta], Abstract:[data error], Detail:[qta res data invalid, error_no: ' . $res['error_no'] . ']', self::SERVICE_NAME, self::$reqsvr);
            return false;
        }

        return $res['enc_data'];
    }

    /**
     * @brief 解密接口,支持批量
     *
     * @param array $arrReqEncodeQid :
     * array(
     *      array('encodeQid' => '555849674618600164'),
     *      ... ...
     * )
     *
     * @return
     * 成功返回
     * array(
     *      array(
     *          'qid'                => 2,
     *          'decode_create_time' => 65374,      //create_time后五位
     *          'decode_check_flag'  => 'pass',     //pass表示校验通过, fail表示校验未通过
     *          'create_time'        => 1118565374,
     *      ),
     *      ... ...
     * )
     * 失败返回false
     * @author zhouzhaopeng
     * @date 2013/08/26 18:00:03
     **/
    public static function decodeQid($arrReqEncodeQid)
    {
        if (empty($arrReqEncodeQid)) {
            return false;
        }

        $rpack = array();
        foreach ($arrReqEncodeQid as $value) {
            $encodeQid = isset($value['encodeQid']) ? strval($value['encodeQid']) : '';
            if (!empty($encodeQid)) {
                $rpack['req_qid'][] = $encodeQid . ':0:0';
            }
        }
        $rpack['req_qid_num'] = count($rpack['req_qid']);

        if ($rpack['req_qid_num'] == 0) {
            return false;
        }

        $rpack['module_name'] = MAIN_APP;
        $rpack['qta_req_type'] = self::QTA_ORDER_TYPE_CLUSTER;
        $rpack['req_atr'] = array(
            'qatr' => array('qid', 'decode_create_time', 'decode_check_flag'),
            'ratr' => array(),
            'rnum' => array(
                'order_type' => 0,
                'pn' => 0,
                'rn' => 0,
            ),
        );
        $rpack['req_atr_num'] = 0;

        //调用QTA服务
        ral_set_pathinfo('');
        ral_set_querystring('');
        ral_set_header('', RAL_SET_HEADER_RESET);
        $res = ral('Qta', MAIN_APP, $rpack, rand());
        if (empty($res)) {
            Bd_Log::fatal('[Component_Base_Qta] Error:[Qta], Abstract:[connect error], Detail:[talk with Qta service failed, error_no : ' . ral_get_errno() . ']', self::SERVICE_NAME, self::$reqsvr);
            return false;
        }

        //数据错误
        if ($res['error_no'] != 0 && $res['error_no'] != 1) {
            Bd_Log::fatal('[Component_Base_Qta] Error:[Qta], Abstract:[data error], Detail:[qta res data invalid, error_no: ' . $res['error_no'] . ']', self::SERVICE_NAME, self::$reqsvr);
            return false;
        }

        return $res['qta_data'];
    }
}

