# 管理员微服务实践
把管理员服务剥离了出来，以微服务的形式对外提供服务，一个账号，即可通行所有接入该服务的项目，便于使用，更便于维护；
[体验地址](http://139.180.133.174:8080)
账号root
密码
# 有哪些功能？
###1.Token鉴权/API级权限管理
登录校验通过后返回一个token。
```
$token = [
            'user' => $user_name,//用户名
            'iat' => time(),//开始时间
            'exp' => time() + 3600*16,//过期时间
        ];
        //签名
        $token['signature'] = $this->openssl->sign($user_name, $token['iat'], $token['exp']);        
        $token['access'] = $this->openssl->privateEncrypt(json_encode($access));//私钥加密权限信息
        $token = json_encode($token);
        return base64_encode($token);
```
在需要鉴权的接口，使用公钥解析此token，即可获得权限信息；
### 2.扫码登录
这个登录的原理是使用了微信内置浏览器的localStorage功能，用户通过微信扫码【绑定令牌】，将自己的身份认证信息储存在自己的微信内置浏览器内（当然其他支持localStorage的app亦可），在用户扫码登录时，跳转到二维码页面，将身份认证信息发送给后端验证，然后client轮询二维码的扫码信息，详细思路见：[PHP扫码登录实现](https://blog.csdn.net/sinat_35880197/article/details/87377186)
###3. 请求记录
在lib.request.php类库里获取请求，并扔进队列，然后依次入库；
### @todo 4.API监控
