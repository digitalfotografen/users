<?php
/**
 * Copyright 2010 - 2015, Cake Development Corporation (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2015, Cake Development Corporation (http://cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace CakeDC\Users\Controller\Traits;

use Cake\Core\Configure;
use Cake\Http\Client;
use Cake\Log\Log;
use Cake\Network\Response;
use \DateTime;

/**
 * Covers SmsKey features
 *
 */
trait SmsKeyTrait
{

    /**
     * Handles sms key form
     * 
     * Calls LoginTrait::_afterIdentifyUser on sucess
     * @return void
     */
    public function smskey()
    {
        if ($this->request->is('post') && isset($this->request->data['smskey'])) {
            $expired = $this->request->session()->read('Smskey.expire') ?? null;
            $secret = $this->request->session()->read('Smskey.secret') ?? null;

            if (empty($secret) || empty($expired)){
                $msg = __d('CakeDC/Users', 'Sms key was never set by system');
                $this->Flash->error($msg);
                return;
            }

            if ($expired < new DateTime("now")){
                $msg = __d('CakeDC/Users', 'Sms key expired');
                $this->Flash->error($msg);
                return;
            }

            if ($this->request->data['smskey'] == $secret){
                $user = $this->request->getSession()->read('Smskey.user');
                return $this->_afterIdentifyUser($user);
            }

            $msg = __d('CakeDC/Users', 'Invalid sms key');
            $this->Flash->error($msg);
        }
    }

    /**
     * Creates new SMS key and stores in session
     * Sends key to user
     *
     * @param Array $user user
     * @return Response|bool Redirects to login on failre
     */
    public function renewSmsKey($user)
    {
        $secret = (string) rand(100000 , 999999); // switch this to random_int when using php7
        $expire = new DateTime("now");
        $expire = $expire->modify( Configure::read('Users.SmsKey.expire', '+30 minutes') );

        if (empty($user) || empty($user['sms'])){
            $message = __d('CakeDC/Users', 'Missing SMS number for this user');
            $this->Flash->error($message, 'default', [], 'auth');
            return $this->redirect(Configure::read('Auth.loginAction'));
        }
        
        $number = $user['sms'];
        $this->request->getSession()->write('Smskey.secret', $secret);
        $this->request->getSession()->write('Smskey.user', $user);
        $this->request->getSession()->write('Smskey.expire', $expire);
        $appName = Configure::read('App.name');
        $sucess = $this->sendSms(
            $number, 
            __('{0} SMS-key: {1}', $appName, $secret)
        );

        if (!$sucess){
            $message = __d('CakeDC/Users', 'Failed to send SMS-key');
            $this->Flash->error($message, 'default', [], 'auth');
            return $this->redirect(Configure::read('Auth.loginAction'));
        }
        return $sucess;
    }
    
    /**
    *
    */
    /**
     * Sends SMS
     *
     * @param string $to sms number with country code
     * @param string $message complete message
     * @return boolean true on sucess
     */
    protected function sendSms($to, $message)
    {
        $http = new Client();
        $config = Configure::read('Users.SmsKey.SmsConfig');
        $url = $config['url'];
        Log::write('debug', print_r($config, true));
        $options = [
            'headers' => [
                'Content-type' => 'application/x-www-form-urlencoded',
            ],
            'auth' => [
                'type' => 'basic',
                'username' => $config['api_username'],
                'password' => $config['api_password'],
            ],
            'type' => 'json',
            'ssl_verify_peer' => false
        ];

        if (!empty($config['Proxy'])) {
            $options['proxy'] = $config['Proxy'];
        }

        $q = [
            'to' => $to,
            'message' => $message,
            'from' => $config['from'],
        ];

        if (isset($config['flashsms'])) {
            $q['flashsms'] = $config['flashsms'];
        }

        $response = $http->post($url, $q, $options);
        
        if (!$response->isOK()){
            Log::write('error', "=== sendSMS failed ===");
            Log::write('error',print_r($response, true));
            return false;
        }
        return true;
    }
    
}

