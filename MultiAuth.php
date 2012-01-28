<?php
namespace MultiAuth; 
/**
 * MultiAuth is a light library to create a powerfull authentification system.
 * An account have some way to log in, with hybrid auth, a way to log in is a couple provider/login.
 * Two account may have a way of login in common. When a user login with a common way of login, he can manage both account.
 * You can have two different twtter application and a account may be linked with this 2 twitter application.
 * Example of use :
 * @author Luc Boissaye
 * @version 1.0
 */

class MultiAuth{

    private $configs = null;

    private $hybridauth = null;
    
    /**
     * Always start by creating a MultiAuth Object.
     * @return void 
     * @author Luc Boissaye
     * @since 1.0
     * @access public
     */
    public function __construct( $configs = null ){
        session_start();
        if( $configs === null ){
            return;
        }
        $this->configs=$configs;
    }

    /**
     * This is the main function, will always return an valid user. If the user is not loggued in, he will be redirected. May also throw an exception.
     * @return a valid user.
     * @author Luc Boissaye
     * @since 1.0
     */
    public function login(){
        $account = $this->getAccount();
        //!TODO A way to customize the parameter provider ?
        if( isset( $_GET['Provider'] ) ){
            //!TODO Check provider !!
            $providerName = $_GET['Provider'];
            $provider = $this->getHybridAuth()->authenticate( $providerName );
            $providerId = $provider->id; 
            if( $provider->isUserConnected() ){

                $providerId = $provider->id; 
                $id = $provider->getUserProfile()->identifier;

                $accounts = $this->callFunctionInConfigs(
                    'accountsFromProviderIdFunction',
                    array(
                        $providerName,
                        $id
                    )
                );
                if( !is_array($accounts) ){
                    //!TODO Exception
                }

                if( $account !== null){
                    if ( !in_array($account,$accounts)) {
                        //We add a way to loggin
                        $this->callFunctionInConfigs(
                            'addProviderToAccount',
                            array(
                                $account,
                                $providerId,
                                $id,
                                $this->getHybridAuth()->getSessionData()
                            )
                        );
                    }
                    //And add All the new Accounts : 
                    $accounts = array_merge($accounts, $this->getAccounts());
                    //save in session
                    $this->setAccounts($accounts);
                    $this->setAccount($account);
                }else{
                    //We loggin
                    if( count($accounts) == 0 ){
                        //Need to create an account
                        $account = $this->callFunctionInConfigs('createAccountFunction');
                        //save in session
                        $accounts = array($account);
                        $this->setAccounts($accounts);
                        $this->setAccount($account);
                        $this->callFunctionInConfigs(
                            'addProviderToAccount',
                            array(
                                $account,
                                $providerId,
                                $id,
                                $this->getHybridAuth()->getSessionData()
                            )
                        );
                    }else{
                        $account = $accounts[0]; 
                        $this->setAccount($account);
                    }
                }
                return $account;
            }
        }
        if( $account !== null ){
            return $account;
        }
        $this->loginPage();
    }

    public function logout(){
        \Hybrid_Auth::logoutAllProviders(); 
        $this->setAccount(null);
        $this->setAccounts(array());
    }


    //Display or redirect to a page with all Login Possibilities
    public function LoginPage(){
        \Hybrid_Auth::logoutAllProviders(); 
        $loginPage = $this->getconfig('loginPage');
        if( $loginPage !== null){
            \Hybrid_Auth::redirect($loginPage,"PHP");
        }
        //!TODO Make an alternative with loginList
        $this->loginList();
        exit;
    }


    //Display a list of login Providers with Images.
    public function LoginList(){
        echo "LOGIN PAGE !!";
        echo '<ul class="login">';
        foreach($this->configs['hybridAuth']['providers'] as $k=>$p){
            echo '<li><a href="?Provider='.$k.'" class="'.$k.'">'.$k.'</a></li>';
        }
        echo '</ul>';
    }



    /**
     * This function will return all possible account for a opened session
     * @return array of accounts
     * @author Luc Boissaye
     * @since 1.0
     * @access public
     */
    public function getAccounts(){
        $accountsId = $this->getSession('sessionAccountsKey',array());
        $accounts = array();
        foreach($accountsId as $id){
            $accounts[] = $this->callFunctionInConfigs(
                'getAccountFunction',
                array($id)
            );
        }
        return $accounts;
    }

    private function setAccounts($accounts){
        $ids = array();
        foreach($accounts as $a){
            $ids[] = $a->getId();
        }
        $this->setSession('sessionAccountsKey',array_unique($ids));
    }


    /**
     * This function will return current selected account.
     * @param var \models\core\element\element
     * @return the current selected account.
     * @author Luc Boissaye
     * @since 1.0
     */
    public function getAccount(){
        $accountId = $this->getSession('sessionAccountKey',null);
        if( !isset($accountId) ){
            return null;
        }
        return $this->callFunctionInConfigs(
            'getAccountFunction',
            array($accountId)
        );
    }
    public function setAccount($account){
        $id = null;
        //!TODO check implements
        if( $account instanceof \MultiAuth\user ){
            $id = $account->getId();
        }
        $accountsId = $this->getSession('sessionAccountKey',array());
        if( !in_array($id, $accountsId)) {
            //!TODO Exception
        }
        $this->setSession('sessionAccountKey',$id);
    }

    public function getConfig($key,$default = null){
        if( !isset($this->configs[$key]) ){
            return $default;
        }
        return $this->configs[$key];

    }

    public function getConfigs(){
        return $this->configs;
    }
    public function setConfigs($configs){
        $this->configs=$configs;
        return $this;
    }

    public function getHybridAuth(){
        if( $this->hybridauth === null ){
            require_once(__DIR__.'/lib/hybridauth/hybridauth/Hybrid/Auth.php');//to check
            $this->hybridauth = new \Hybrid_Auth( $this->configs['hybridAuth'] );
        }
        return $this->hybridauth;
    }

    public function setHybridAuth($hybridauth){
        $this->hybridauth=$hybridauth;
        return $this;
    }

    private function translateKey($key){
        return $this->getConfig($key,'MultiAuth-'.$key);
    }

    private function initSession(){
    }
    private function getSession($key, $default = null){
        $translatedKey = $this->translateKey($key);
        if( !isset($_SESSION['MultiAuth'] ) ){
            $_SESSION['MultiAuth'] = array();
        }
        if( isset($_SESSION['MultiAuth'][$translatedKey]) ){
            $value = unserialize($_SESSION['MultiAuth'][$translatedKey]);
        }else{
            return null;
        }
        //$value = \Hybrid_Auth::storage()->get($translatedKey);
        return $value;
    }

    private function setSession($key,$value){
        $translatedKey = $this->translateKey($key);
        if( !isset($_SESSION['MultiAuth'] ) ){
            $_SESSION['MultiAuth'] = array();
        }
        $_SESSION['MultiAuth'][$translatedKey] = serialize($value);
        //\Hybrid_Auth::storage()->set($translatedKey,$value);
        return $this;
    }
    private function callFunctionInConfigs($functionName, $params = array()){
        //echo 'call :';
        //!TODO make a call on the function.
        $function = $this->getConfig(
            $functionName,null
        );
        if( !is_callable($function) ){
            //!TODO Exception
        }
        $res = call_user_func_array($function,$params);
        return $res;
    }

    /**
     * This function will return the HybridAuthProvider
     * @param string $providerName
     * @param string $datas
     * @return \HybridAuth\Provider
     * @author Luc Boissaye
     * @since 1.0
     * @access public
     */
    public function getHybridAuthProvider($providerName,$datas){
        $this->getHybridAuth()->restoreSessionData($datas);
        return $this->getHybridAuth()->getAdapter($providerName);
    }
}

?>
