<?php
namespace hxy2015\oss;

use Yii;
use yii\base\Component;

/**
 * @author huangxy <huangxy10@qq.com>
 */
class Connection extends Component
{
    /*%******************************************************************************************%*/
    //OSS 内部常量

    const OSS_BUCKET = 'bucket';
    const OSS_OBJECT = 'object';
    const OSS_HEADERS = 'headers';
    const OSS_METHOD = 'method';
    const OSS_QUERY = 'query';
    const OSS_BASENAME = 'basename';
    const OSS_MAX_KEYS = 'max-keys';
    const OSS_UPLOAD_ID = 'uploadId';
    const OSS_MAX_KEYS_VALUE = 100;
    const OSS_MAX_OBJECT_GROUP_VALUE = 1000;
    const OSS_FILE_SLICE_SIZE = 8192;
    const OSS_PREFIX = 'prefix';
    const OSS_DELIMITER = 'delimiter';
    const OSS_MARKER = 'marker';
    const OSS_CONTENT_MD5 = 'Content-Md5';
    const OSS_CONTENT_TYPE = 'Content-Type';
    const OSS_CONTENT_LENGTH = 'Content-Length';
    const OSS_IF_MODIFIED_SINCE = 'If-Modified-Since';
    const OSS_IF_UNMODIFIED_SINCE = 'If-Unmodified-Since';
    const OSS_IF_MATCH = 'If-Match';
    const OSS_IF_NONE_MATCH = 'If-None-Match';
    const OSS_CACHE_CONTROL = 'Cache-Control';
    const OSS_EXPIRES = 'Expires';
    const OSS_PREAUTH = 'preauth';
    const OSS_CONTENT_COING = 'Content-Coding';
    const OSS_CONTENT_DISPOSTION = 'Content-Disposition';
    const OSS_RANGE = 'Range';
    const OS_CONTENT_RANGE = 'Content-Range';
    const OSS_CONTENT = 'content';
    const OSS_BODY = 'body';
    const OSS_LENGTH = 'length';
    const OSS_HOST = 'Host';
    const OSS_DATE = 'Date';
    const OSS_AUTHORIZATION = 'Authorization';
    const OSS_FILE_DOWNLOAD = 'fileDownload';
    const OSS_FILE_UPLOAD = 'fileUpload';
    const OSS_PART_SIZE = 'partSize';
    const OSS_SEEK_TO = 'seekTo';
    const OSS_SIZE = 'size';
    const OSS_QUERY_STRING = 'query_string';
    const OSS_SUB_RESOURCE = 'sub_resource';
    const OSS_DEFAULT_PREFIX = 'X-QS-';
    const OSS_PART_NUM = 'partNumber';

    /*%******************************************************************************************%*/
    //私有URL变量

    const OSS_URL_ACCESS_KEY_ID = 'OSSAccessKeyId';
    const OSS_URL_EXPIRES = 'Expires';
    const OSS_URL_SIGNATURE = 'Signature';

    /*%******************************************************************************************%*/
    //HTTP方法

    const OSS_HTTP_GET = 'GET';
    const OSS_HTTP_PUT = 'PUT';
    const OSS_HTTP_HEAD = 'HEAD';
    const OSS_HTTP_POST = 'POST';
    const OSS_HTTP_DELETE = 'DELETE';
    const OSS_HTTP_OPTIONS = 'OPTIONS';

    /*%******************************************************************************************%*/
    //其他常量

    //x-oss
    const OSS_ACL = 'x-oss-acl';

    //OBJECT GROUP
    const OSS_OBJECT_GROUP = 'x-oss-file-group';

    //Multi Part
    const OSS_MULTI_PART = 'uploads';

    //Multi Delete
    const OSS_MULTI_DELETE = 'delete';

    //OBJECT COPY SOURCE
    const OSS_OBJECT_COPY_SOURCE = 'x-oss-copy-source';

    //private,only owner
    const OSS_ACL_TYPE_PRIVATE = 'private';

    //public reand
    const OSS_ACL_TYPE_PUBLIC_READ = 'public-read';

    //public read write
    const OSS_ACL_TYPE_PUBLIC_READ_WRITE = 'public-read-write';

    //OSS ACL数组
    static $OSS_ACL_TYPES = array(
        self::OSS_ACL_TYPE_PRIVATE,
        self::OSS_ACL_TYPE_PUBLIC_READ,
        self::OSS_ACL_TYPE_PUBLIC_READ_WRITE
    );

    const OSS_CORS_ALLOWED_ORIGIN = 'AllowedOrigin';
    const OSS_CORS_ALLOWED_METHOD = 'AllowedMethod';
    const OSS_CORS_ALLOWED_HEADER = 'AllowedHeader';
    const OSS_CORS_EXPOSE_HEADER = 'ExposeHeader';
    const OSS_CORS_MAX_AGE_SECONDS = 'MaxAgeSeconds';


    const OSS_OPTIONS_ORIGIN = 'Origin';
    const OSS_OPTIONS_REQUEST_METHOD = 'Access-Control-Request-Method';
    const OSS_OPTIONS_REQUEST_HEADERS = 'Access-Control-Request-Headers';

    /*%******************************************************************************************%*/
    // PROPERTIES

    /**
     * 是否开启debug模式
     */
    public $debug_mode = false;

    /**
     * 最大重试次数
     */
    public $max_retries = 3;

    /**
     * 已经重试次数
     */
    public $redirects = 0;

    /**
     * 虚拟地址
     */
    public $vhost;

    /**
     * 路径表现方式
     */
    public $enable_domain_style = false;

    /**
     * 请求URL
     */
    public $request_url;

    /**
     * OSS API ACCESS ID
     */
    public $accessId;

    /**
     * OSS API ACCESS KEY
     */
    public $accessKey;

    /**
     * hostname
     */
    public $hostname = 'localhost';

    /**
     * @var string
     */
    public $bucket;

    /**
     * @var float timeout to use for connecting to an elasticsearch node.
     * This value will be used to configure the curl `CURLOPT_CONNECTTIMEOUT` option.
     * If not set, no explicit timeout will be set for curl.
     */
    public $connectionTimeout = null;
    /**
     * @var float timeout to use when reading the response from an elasticsearch node.
     * This value will be used to configure the curl `CURLOPT_TIMEOUT` option.
     * If not set, no explicit timeout will be set for curl.
     */
    public $dataTimeout = null;


    public function init()
    {
        //校验access_id&access_key
        if (empty($this->accessId) || empty($this->accessKey)) {
            throw new OssException('OSS_ACCESS_ID_OR_ACCESS_KEY_EMPTY');
        }

        if (empty($this->bucket)) {
            throw new OssException('BUCKET_EMPTY');
        }
    }

    /*%******************************************************************************************************%*/
    //请求

    /**
     * Authorization
     * @param array $options (Required)
     * @return OssResponse
     * @throws OssException
     */
    public function auth($options)
    {
        //开始记录LOG
        $msg = "---LOG START---------------------------------------------------------------------------\n";

        //验证Bucket,list_bucket时不需要验证
        if (!(('/' == $options[self::OSS_OBJECT]) && ('' == $options[self::OSS_BUCKET]) && ('GET' == $options[self::OSS_METHOD])) && !$this->validate_bucket($options[self::OSS_BUCKET])) {
            throw new OssException('"' . $options[self::OSS_BUCKET] . '"' . 'OSS_BUCKET_NAME_INVALID');
        }

        //验证Object
        if (isset($options[self::OSS_OBJECT]) && !$this->validate_object($options[self::OSS_OBJECT])) {
            throw  new OssException($options[self::OSS_OBJECT] . 'OSS_OBJECT_NAME_INVALID');
        }

        //Object编码为UTF-8
        if ($this->is_gb2312($options[self::OSS_OBJECT])) {
            $options[self::OSS_OBJECT] = iconv('GB2312', "UTF-8", $options[self::OSS_OBJECT]);
        } elseif ($this->check_char($options[self::OSS_OBJECT], true)) {
            //$options[self::OSS_OBJECT] = iconv('GBK', "UTF-8",$options[self::OSS_OBJECT]);
        }


        //验证ACL
        if (isset($options[self::OSS_HEADERS][self::OSS_ACL]) && !empty($options[self::OSS_HEADERS][self::OSS_ACL])) {
            if (!in_array(strtolower($options[self::OSS_HEADERS][self::OSS_ACL]), self::$OSS_ACL_TYPES)) {
                throw new OssException($options[self::OSS_HEADERS][self::OSS_ACL] . ':' . 'OSS_ACL_INVALID');
            }
        }

        if ($this->enable_domain_style) {
            $hostname = $this->vhost ? $this->vhost : (($options[self::OSS_BUCKET] == '') ? $this->hostname : ($options[self::OSS_BUCKET] . '.') . $this->hostname);
        } else {
            $hostname = (isset($options[self::OSS_BUCKET]) && '' !== $options[self::OSS_BUCKET]) ? $this->hostname . '/' . ($options[self::OSS_BUCKET]) : $this->hostname;
        }


        //请求参数
//        $resource = '';
//        $sub_resource = '';
        $signable_resource = '';
        $query_string_params = array();
        $signable_query_string_params = array();
        $string_to_sign = '';

        $headers = array(
            self::OSS_CONTENT_MD5  => '',
            self::OSS_CONTENT_TYPE => isset($options[self::OSS_CONTENT_TYPE]) ? $options[self::OSS_CONTENT_TYPE] : 'application/x-www-form-urlencoded',
            self::OSS_DATE         => isset($options[self::OSS_DATE]) ? $options[self::OSS_DATE] : gmdate('D, d M Y H:i:s \G\M\T'),
            self::OSS_HOST         => $this->enable_domain_style ? $hostname : $this->hostname,
        );

        if (isset ($options [self::OSS_OBJECT]) && '/' !== $options [self::OSS_OBJECT]) {
            //$options[self::OSS_OBJECT] = $this->replace_invalid_xml_char($options[self::OSS_OBJECT]);
            $signable_resource = '/' . str_replace(array('%2F', '%25'), array('/', '%'), rawurlencode($options[self::OSS_OBJECT]));
        }

        if (isset($options[self::OSS_QUERY_STRING])) {
            $query_string_params = array_merge($query_string_params, $options[self::OSS_QUERY_STRING]);
        }
        $query_string = $this->to_query_string($query_string_params);

        $signable_list = array(
            'partNumber',
            'uploadId',
        );

        foreach ($signable_list as $item) {
            if (isset($options[$item])) {
                $signable_query_string_params[$item] = $options[$item];
            }
        }
        $signable_query_string = $this->to_query_string($signable_query_string_params);

        //合并 HTTP headers
        if (isset ($options [self::OSS_HEADERS])) {
            $headers = array_merge($headers, $options [self::OSS_HEADERS]);
        }

        //生成请求URL
        $conjunction = '?';

        $non_signable_resource = '';

        if (isset($options[self::OSS_SUB_RESOURCE])) {
            $signable_resource .= $conjunction . $options[self::OSS_SUB_RESOURCE];
            $conjunction = '&';
        }

        if ($signable_query_string !== '') {
            $signable_query_string = $conjunction . $signable_query_string;
            $conjunction = '&';
        }

        if ($query_string !== '') {
            $non_signable_resource .= $conjunction . $query_string;
            $conjunction = '&';
        }

        $this->request_url = 'http://' . $hostname . $signable_resource . $signable_query_string . $non_signable_resource;

        $msg .= "--REQUEST URL:----------------------------------------------\n" . $this->request_url . "\n";

        //创建请求
        $request = new OssRequest([
            'requestUrl' => $this->request_url,
            'connectionTimeout' => $this->connectionTimeout,
            'dataTimeout' => $this->dataTimeout,
        ]);

        // Streaming uploads
        if (isset($options[self::OSS_FILE_UPLOAD])) {
            if (is_resource($options[self::OSS_FILE_UPLOAD])) {
                $length = null;

                if (isset($options[self::OSS_CONTENT_LENGTH])) {
                    $length = $options[self::OSS_CONTENT_LENGTH];
                } elseif (isset($options[self::OSS_SEEK_TO])) {

                    $stats = fstat($options[self::OSS_FILE_UPLOAD]);

                    if ($stats && $stats[self::OSS_SIZE] >= 0) {
                        $length = $stats[self::OSS_SIZE] - (integer)$options[self::OSS_SEEK_TO];
                    }
                }

                $request->set_read_stream($options[self::OSS_FILE_UPLOAD], $length);

                if ($headers[self::OSS_CONTENT_TYPE] === 'application/x-www-form-urlencoded') {
                    $headers[self::OSS_CONTENT_TYPE] = 'application/octet-stream';
                }
            } else {
                $request->set_read_file($options[self::OSS_FILE_UPLOAD]);

                $length = $request->readStreamSize;

                if (isset($options[self::OSS_CONTENT_LENGTH])) {
                    $length = $options[self::OSS_CONTENT_LENGTH];
                } elseif (isset($options[self::OSS_SEEK_TO]) && isset($length)) {
                    $length -= (integer)$options[self::OSS_SEEK_TO];
                }

                $request->set_read_streamSize($length);

                if (isset($headers[self::OSS_CONTENT_TYPE]) && ($headers[self::OSS_CONTENT_TYPE] === 'application/x-www-form-urlencoded')) {
                    $extension = explode('.', $options[self::OSS_FILE_UPLOAD]);
                    $extension = array_pop($extension);
                    $mime_type = MimeTypes::get_mimetype($extension);
                    $headers[self::OSS_CONTENT_TYPE] = $mime_type;
                }
            }

            $options[self::OSS_CONTENT_MD5] = '';
        }

        if (isset($options[self::OSS_SEEK_TO])) {
            $request->set_seek_position((integer)$options[self::OSS_SEEK_TO]);
        }

        if (isset($options[self::OSS_FILE_DOWNLOAD])) {
            if (is_resource($options[self::OSS_FILE_DOWNLOAD])) {
                $request->set_write_stream($options[self::OSS_FILE_DOWNLOAD]);
            } else {
                $request->set_write_file($options[self::OSS_FILE_DOWNLOAD]);
            }
        }


        if (isset($options[self::OSS_METHOD])) {
            $request->set_method($options[self::OSS_METHOD]);
            $string_to_sign .= $options[self::OSS_METHOD] . "\n";
        }

        if (isset ($options [self::OSS_CONTENT])) {
            $request->set_body($options [self::OSS_CONTENT]);
            if ($headers[self::OSS_CONTENT_TYPE] === 'application/x-www-form-urlencoded') {
                $headers[self::OSS_CONTENT_TYPE] = 'application/octet-stream';
            }

            $headers[self::OSS_CONTENT_LENGTH] = strlen($options [self::OSS_CONTENT]);
            $headers[self::OSS_CONTENT_MD5] = $this->hex_to_base64(md5($options[self::OSS_CONTENT]));
        }

        uksort($headers, 'strnatcasecmp');

        foreach ($headers as $header_key => $header_value) {
            $header_value = str_replace(array("\r", "\n"), '', $header_value);
            if ($header_value !== '') {
                $request->add_header($header_key, $header_value);
            }

            if (
                strtolower($header_key) === 'content-md5' ||
                strtolower($header_key) === 'content-type' ||
                strtolower($header_key) === 'date' ||
                (isset($options['self::OSS_PREAUTH']) && (integer)$options['self::OSS_PREAUTH'] > 0)
            ) {
                $string_to_sign .= $header_value . "\n";
            } elseif (substr(strtolower($header_key), 0, 5) === self::OSS_DEFAULT_PREFIX) {
                $string_to_sign .= strtolower($header_key) . ':' . $header_value . "\n";
            }
        }

        $string_to_sign .= '/' . $options[self::OSS_BUCKET];
        $string_to_sign .= $this->enable_domain_style ? ($options[self::OSS_BUCKET] != '' ? ($options[self::OSS_OBJECT] == '/' ? '' : '') : '') : '';
        $string_to_sign .= rawurldecode($signable_resource) . urldecode($signable_query_string);

        $msg .= "STRING TO SIGN:----------------------------------------------\n" . $string_to_sign . "\n";

        $signature = base64_encode(hash_hmac('sha256', $string_to_sign, $this->accessKey, true));
        $request->add_header('Authorization', 'QS-HMAC-SHA256 ' . $this->accessId . ':' . $signature);

        if (isset($options[self::OSS_PREAUTH]) && (integer)$options[self::OSS_PREAUTH] > 0) {
            return $this->request_url . $conjunction . self::OSS_URL_ACCESS_KEY_ID . '=' . $this->accessId . '&' . self::OSS_URL_EXPIRES . '=' . $options[self::OSS_PREAUTH] . '&' . self::OSS_URL_SIGNATURE . '=' . rawurlencode($signature);
        } elseif (isset($options[self::OSS_PREAUTH])) {
            return $this->request_url;
        }

        if ($this->debug_mode) {
            $request->debugMode = $this->debug_mode;
        }

        $msg .= "REQUEST HEADERS:----------------------------------------------\n" . serialize($request->requestHeaders) . "\n";

        $request->send_request();

        $response_header = $request->get_response_header();
        $response_header['x-qc-request-url'] = $this->request_url;
        $response_header['x-qc-redirects'] = $this->redirects;
        $response_header['x-qc-stringtosign'] = $string_to_sign;
        $response_header['x-qc-requestheaders'] = $request->requestHeaders;

        $msg .= "RESPONSE HEADERS:----------------------------------------------\n" . serialize($response_header) . "\n";

        $data = new OssResponse ($response_header, $request->get_response_body(), $request->get_response_code());

        Yii::info($response_header, __METHOD__ . 'response_header');
        Yii::info($request->get_response_code(), __METHOD__ . ' get_response_code');


        if ((integer)$request->get_response_code() === 400 /*Bad Request*/ || (integer)$request->get_response_code() === 500 /*Internal Error*/ || (integer)$request->get_response_code() === 503 /*Service Unavailable*/) {
            if ($this->redirects <= $this->max_retries) {
                //设置休眠
                $delay = (integer)(pow(4, $this->redirects) * 100000);
                Yii::info($this->redirects, __METHOD__ . 'redirects');
                Yii::info($delay, __METHOD__ . "delay");
                usleep($delay);
                $this->redirects++;
                $data = $this->auth($options);
            }
        }

        $msg .= "RESPONSE DATA:----------------------------------------------\n" . serialize($data) . "\n";
        $msg .= date('Y-m-d H:i:s') . ":---LOG END---------------------------------------------------------------------------\n";

        Yii::trace($msg, __METHOD__);

        $this->redirects = 0;
        return $data;
    }

    /*%******************************************************************************************************%*/
    //Object Operation

    /**
     * 通过在http body中添加内容来上传文件，适合比较小的文件
     * 根据api约定，需要在http header中增加content-length字段
     *
     * @param string $object (Required)
     * @param string $content (Required)
     * @param array $options (Optional)
     *
     * @return OssResponse
     */
    public function putObjectByContent($object, $content, $options=[])
    {
        $bucket = $this->bucket;

        $options = array_merge($options, [
            'content' => $content,
            'length' => strlen($content),
        ]);


        //options
        $this->validate_options($options);

        if (!$options) {
            $options = array();
        }

        //bucket
        $this->is_empty($bucket, 'OSS_BUCKET_IS_NOT_ALLOWED_EMPTY');

        //object
        $this->is_empty($object, 'OSS_OBJECT_IS_NOT_ALLOWED_EMPTY');

        //内容校验
        $this->validate_content($options);


        $objArr = explode('/', $object);
        $basename = array_pop($objArr);
        $extension = explode('.', $basename);
        $extension = array_pop($extension);
        $content_type = MimeTypes::get_mimetype(strtolower($extension));

        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_PUT;
        $options[self::OSS_OBJECT] = $object;

        if (!isset($options[self::OSS_LENGTH])) {
            $options[self::OSS_CONTENT_LENGTH] = strlen($options[self::OSS_CONTENT]);
        } else {
            $options[self::OSS_CONTENT_LENGTH] = $options[self::OSS_LENGTH];
        }

        if (!isset($options[self::OSS_CONTENT_TYPE]) && isset($content_type) && !empty($content_type)) {
            $options[self::OSS_CONTENT_TYPE] = $content_type;
        }

        $response = $this->auth($options);

        return $response->status == 201;
    }

    /**
     * 上传文件，适合比较大的文件
     *
     * @param string $object (Required)
     * @param string $file (Required)
     * @param array $options (Optional)
     * @return OssResponse
     * @throws OssException
     */
    public function putObjectByFile($object, $file, $options=[])
    {
        $bucket = $this->bucket;

        //options
        $this->validate_options($options);

        if (!$options) {
            $options = array();
        }

        //bucket
        $this->is_empty($bucket, 'OSS_BUCKET_IS_NOT_ALLOWED_EMPTY');

        //object
        $this->is_empty($object, 'OSS_OBJECT_IS_NOT_ALLOWED_EMPTY');

        //file
        $this->is_empty($file, 'OSS_FILE_PATH_IS_NOT_ALLOWED_EMPTY');

        if ($this->chk_chinese($file)) {
            $file = iconv('utf-8', 'gbk', $file);
        }

        $options[self::OSS_FILE_UPLOAD] = $file;

        if (!file_exists($options[self::OSS_FILE_UPLOAD])) {
            throw new OssException($options[self::OSS_FILE_UPLOAD] . 'OSS_FILE_NOT_EXIST');
        }

        $filesize = filesize($options[self::OSS_FILE_UPLOAD]);
        $extension = explode('.', $file);
        $extension = array_pop($extension);
        $content_type = MimeTypes::get_mimetype(strtolower($extension));

        $options[self::OSS_METHOD] = self::OSS_HTTP_PUT;
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_OBJECT] = $object;
        $options[self::OSS_CONTENT_TYPE] = $content_type;
        $options[self::OSS_CONTENT_LENGTH] = $filesize;

        $response = $this->auth($options);
        return $response->status == 201;
    }


    /**
     * 获得Object内容
     *
     * @param string $object (Required)
     * @param array $options (Optional)
     * @return OssResponse
     */
    public function getObject($object, $options=[])
    {
        $bucket = $this->bucket;

        //options
        $this->validate_options($options);

        if (!$options) {
            $options = array();
        }

        //bucket
        $this->is_empty($bucket, 'OSS_BUCKET_IS_NOT_ALLOWED_EMPTY');

        //object
        $this->is_empty($object, 'OSS_OBJECT_IS_NOT_ALLOWED_EMPTY');

        if (isset($options[self::OSS_FILE_DOWNLOAD]) && $this->chk_chinese($options[self::OSS_FILE_DOWNLOAD])) {
            $options[self::OSS_FILE_DOWNLOAD] = iconv('utf-8', 'gbk', $options[self::OSS_FILE_DOWNLOAD]);
        }

        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_GET;
        $options[self::OSS_OBJECT] = $object;

        if (isset($options['lastmodified'])) {
            $options[self::OSS_HEADERS][self::OSS_IF_MODIFIED_SINCE] = $options['lastmodified'];
            unset($options['lastmodified']);
        }

        if (isset($options['etag'])) {
            $options[self::OSS_HEADERS][self::OSS_IF_NONE_MATCH] = $options['etag'];
            unset($options['etag']);
        }

        if (isset($options['range'])) {
            $options[self::OSS_HEADERS][self::OSS_RANGE] = 'bytes=' . $options['range'];
            unset($options['range']);
        }

        return $this->auth($options);
    }

    public function getObjectContent($object)
    {
        $response = $this->getObject($object);

        return $response->body;
    }

    public function getObjectFile($object, $file)
    {
        $options = [
            'fileDownload' => $file
        ];
        $response = $this->getObject($object, $options);
        return $response->status == 200;
    }

    /**
     * 检测Object是否存在
     *
     * @param string $object (Required)
     * @param array $options (Optional)
     * @return boolean
     */
    public function isObjectExist($object, $options = NULL)
    {
        $bucket = $this->bucket;

        //options
        $this->validate_options($options);

        if (!$options) {
            $options = array();
        }

        //bucket
        $this->is_empty($bucket, 'OSS_BUCKET_IS_NOT_ALLOWED_EMPTY');

        //object
        $this->is_empty($object, 'OSS_OBJECT_IS_NOT_ALLOWED_EMPTY');

        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_GET;
        $options[self::OSS_OBJECT] = $object;

        $response = $this->get_object_meta($bucket, $object, $options);

        return $response->status == 200;
    }

    /**
     * 获得object的meta信息
     * 
     * @param string $bucket (Required)
     * @param string $object (Required)
     * @param string $options (Optional)
     * @return OssResponse
     */
    private function get_object_meta($bucket, $object, $options = NULL)
    {
        //options
        $this->validate_options($options);

        if (!$options) {
            $options = array();
        }

        //bucket
        $this->is_empty($bucket, 'OSS_BUCKET_IS_NOT_ALLOWED_EMPTY');

        //object
        $this->is_empty($object, 'OSS_OBJECT_IS_NOT_ALLOWED_EMPTY');

        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_HEAD;
        $options[self::OSS_OBJECT] = $object;

        $response = $this->auth($options);

        return $response;
    }

    /*%******************************************************************************************************%*/
    //工具类相关

    /**
     * 生成query params
     *
     * @param array $options
     * @return string 返回诸如 key1=value1&key2=value2
     */
    private function to_query_string($options = [])
    {
        $temp = [];

        foreach ($options as $key => $value) {
            if (is_string($key) && !is_array($value)) {
                $temp[] = rawurlencode($key) . '=' . rawurlencode($value);
            }
        }

        return implode('&', $temp);
    }

    /**
     * 转化十六进制的数据为base64
     *
     * @param string $str (Required) 要转化的字符串
     * @return string Base64-encoded string.
     */
    private function hex_to_base64($str)
    {
        $result = '';

        for ($i = 0; $i < strlen($str); $i += 2) {
            $result .= chr(hexdec(substr($str, $i, 2)));
        }

        return base64_encode($result);
    }

    /**
     * 检测是否含有中文
     *
     * @param $str
     * @return bool
     */
    private function chk_chinese($str)
    {
        return preg_match('/[\x80-\xff]./', $str);
    }

    /**
     * 检测是否GB2312编码
     *
     * @param string $str
     * @return boolean false UTF-8编码  TRUE GB2312编码
     */
    private function is_gb2312($str)
    {
        for ($i = 0; $i < strlen($str); $i++) {
            $v = ord($str[$i]);
            if ($v > 127) {
                if (($v >= 228) && ($v <= 233)) {
                    if (($i + 2) >= (strlen($str) - 1)) {
                        return true;
                    }  // not enough characters
                    $v1 = ord($str[$i + 1]);
                    $v2 = ord($str[$i + 2]);
                    if (($v1 >= 128) && ($v1 <= 191) && ($v2 >= 128) && ($v2 <= 191)) {
                        return false;   //UTF-8编码
                    } else {
                        return true;    //GB编码
                    }
                }
            }
        }
        return false;
    }


    /**
     * 检测是否GBK编码
     *
     * @param string $str
     * @param boolean $gbk
     * @return boolean
     */
    private function check_char($str, $gbk = true)
    {
        for ($i = 0; $i < strlen($str); $i++) {
            $v = ord($str[$i]);
            if ($v > 127) {
                if (($v >= 228) && ($v <= 233)) {
                    if (($i + 2) >= (strlen($str) - 1)) return $gbk ? true : FALSE;  // not enough characters
                    $v1 = ord($str[$i + 1]);
                    $v2 = ord($str[$i + 2]);
                    if ($gbk) {
                        return (($v1 >= 128) && ($v1 <= 191) && ($v2 >= 128) && ($v2 <= 191)) ? FALSE : TRUE;//GBK
                    } else {
                        return (($v1 >= 128) && ($v1 <= 191) && ($v2 >= 128) && ($v2 <= 191)) ? TRUE : FALSE;
                    }
                }
            }
        }
        return $gbk ? TRUE : FALSE;
    }


    /**
     * 读取目录
     * @param string $dir (Required) 目录名
     * @param string $exclude
     * @param boolean $recursive (Optional) 是否递归，默认为false
     * @return array
     */
    private function read_dir($dir, $exclude = ".|..|.svn", $recursive = false)
    {
        static $file_list_array = array();

        $exclude_array = explode("|", $exclude);
        //读取目录
        if ($handle = opendir($dir)) {
            while (false !== ($file = readdir($handle))) {
                if (!in_array(strtolower($file), $exclude_array)) {
                    $new_file = $dir . '/' . $file;
                    if (is_dir($new_file) && $recursive) {
                        $this->read_dir($new_file, $exclude, $recursive);
                    } else {
                        $file_list_array[] = array(
                            'path' => $new_file,
                            'file' => $file,
                        );
                    }
                }
            }

            closedir($handle);
        }

        return $file_list_array;
    }

    /**
     * 检验bucket名称是否合法
     * bucket的命名规范：
     * 1. 只能包括小写字母，数字
     * 2. 必须以小写字母或者数字开头
     * 3. 长度必须在3-63字节之间
     * @param string $bucket (Required)
     * @return boolean
     */
    private function validate_bucket($bucket)
    {
        $pattern = '/^[a-z0-9][a-z0-9-]{2,62}$/';
        if (!preg_match($pattern, $bucket)) {
            return false;
        }
        return true;
    }

    /**
     * 检验object名称是否合法
     * object命名规范:
     * 1. 规则长度必须在1-1023字节之间
     * 2. 使用UTF-8编码
     * @param string $object (Required)
     * @return boolean
     */
    private function validate_object($object)
    {
        $pattern = '/^.{1,1023}$/';
        if (empty ($object) || !preg_match($pattern, $object)) {
            return false;
        }
        return true;
    }

    /**
     * 检验$options
     *
     * @param array $options (Optional)
     * @throws OssException
     * @return boolean
     */
    private function validate_options($options)
    {
        //$options
        if ($options != NULL && !is_array($options)) {
            throw new OssException ($options . ':' . 'OSS_OPTIONS_MUST_BE_ARRAY');
        }
    }

    /**
     * 检测上传文件的内容
     * @param array $options (Optional)
     * @throws OssException
     * @return string
     */
    private function validate_content($options)
    {
        if (isset($options[self::OSS_CONTENT])) {
            if ($options[self::OSS_CONTENT] == '' || !is_string($options[self::OSS_CONTENT])) {
                throw new OssException('OSS_INVALID_HTTP_BODY_CONTENT', '-600');
            }
        } else {
            throw new OssException('OSS_NOT_SET_HTTP_CONTENT', '-601');
        }
    }

    /**
     * 校验BUCKET/OBJECT/OBJECT GROUP是否为空
     *
     * @param  string $name (Required)
     * @param  string $errMsg (Required)
     * @throws OssException
     * @return void
     */
    private function is_empty($name, $errMsg)
    {
        if (empty($name)) {
            throw new OssException($errMsg);
        }
    }
}