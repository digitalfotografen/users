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
     */
    public function smskey()
    {
        if ($this->request->is('post')) {
            if (isset($this->request->data['smskey'])){
                $secret = $this->request->session()->read('Smskey.secret');
                if ($this->request->data['smskey'] == $secret){
                    $user = $this->request->session()->read('Smskey.user');
                    return $this->_afterIdentifyUser($user);
                }
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
     * @return void
     */
    public function renewSmsKey(Array $user)
    {
        $secret = (string) random_int(100000 , 999999);
        
        if (empty($user) || empty($user['sms'])){
            $message = __d('CakeDC/Users', 'Missing SMS number for this user');
            $this->Flash->error($message, 'default', [], 'auth');
            return $this->redirect(Configure::read('Auth.loginAction'));
        }
        
        $number = $user['sms'];
        $this->request->session()->write('Smskey.secret', $secret);
        $this->request->session()->write('Smskey.user', $user);
        $this->request->session()->write('Smskey.user', $user);
        $appName = Configure::read('App.name');
        $sucess = $this->_sendSms(
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
    protected function _sendSms(string $to, string $message)
    {
        $http = new Client();
        $config = Configure::read('Users.SmsKey.SmsConfig');
        $url = $config['url'];
        Log::write('debug',print_r($config, true));
        $response = $http->post($url, 
            [   
                'to' => $to, 
                'message' => $message,
                'from' => $config['from'],
                'flashsms' => $config['flashsms'],
            ],
            [
                'headers' => [
                    'Content-type' => 'application/x-www-form-urlencoded',
                ],
                'auth' => [
                    'type' => 'basic',
                    'username' => $config['api_username'], 
                    'password' => $config['api_password'],
                ],
            ]
        );
        
        if (!$response->isOK()){
            Log::write('error', "=== sendSMS failed ===");
            Log::write('error',print_r($response, true));
            return false;
        }
        return true;
    }
    
}

