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
        if( $configs === null ){
            return;
        }
        $this->configs=$configs;
        //!TODO do we do that here ?
        require_once(__DIR__ '/lib/hybridAuth/hybridAuth/Hybrid/Auth.php');//to check
        $this->hybridauth = new Hybrid_Auth( $this->configs['hybridauth'] );

    }

    /**
     * This is the main function, will always return an valid user. If the user is not loggued in, he will be redirected. May also throw an exception.
     * @return a valid user.
     * @author Luc Boissaye
     * @since 1.0
     */
    public function Login(){
        $account = $this->getAccount();
        //!TODO A way to customize the parameter provider ?
        if( isset( $_GET['Provider'] ) ){
            $providerName = $_GET['Provider'];
            //$hybrid auth only here with the right provider ??
            $provider = $hybridauth->authenticate( $providerName );
            if( $provider->isaccountConnected() ){
                //Fetch accounts
                $accounts = $this->callFunctionInConfigs('accountsFromProviderIdFunction');
                if( !is_array($account) ){
                    //!TODO Exception
                }
                if( $account !== null && !in_array($account, $accounts)){
                    $account->addProvider($providerName, $provider->id, $hybridauth->getSessionData());
                    $accounts[]=$account;
                }
                if( count($accounts) == 0 ){
                    $account = $this->callFunctionInConfigs('createaccountFunction');
                    $accounts = array($account);
                }
                //save in session
                $this->setAccounts($accounts);
                $this->setAccount($account);
                return $account;
            }
        }
        if( $account !== null ){
            return $account;
        }
        $this->loginPage();
    }


    //Display or redirect to a page with all Login Possibilities
    public function LoginPage(){
        $loginPage = $this->getconfig('loginPage');
        if( $loginPage !== null){
            Hybrid_Auth::redirect($loginPage,"PHP");
        }
        //!TODO Make an alternative with loginList
    }


    //Display a list of login Providers with Images.
    public function LoginList(){
        
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
        foreach($account as $id){
            $accounts[] = $this->callFunctionInConfigs(
                'getAccountFunction',
                array($id)
            );
        }
        return $accounts;
    }

    public function setAccount($account){
        //!TODO Check on account.
        $id = $account->getId();
        $accountsId = $this->getSession('sessionAccountsKey',array());
        if( !in_array($id, $accountsId)) {
            //!TODO Exception
        }
        $accountsId = $this->setSession('sessionAccountKey',$id);
    }
    /**
     * This function will return current selected account.
     * @param var \models\core\element\element
     * @return the current selected account.
     * @author Luc Boissaye
     * @since 1.0
     */
    public function getAccount(){
        $accountsId = $this->getSession('sessionAccountKey',null);
        if( $accountId === null ){
            return null;
        }
        return $this->callFunctionInConfigs(
            'getAccountFunction',
            array($accountId)
        );
    }

    public function getConfig($key,$default = null){
        if( !isset($this->configs['sessionKey']) ){
            return $default;
        }
        return $this->configs['sessionKey'];

    }

    public function getConfigs(){
        return $this->configs;
    }
    public function setConfigs($configs){
        $this->configs=$configs;
        return $this;
    }

    public function getHybridAuth(){
        return $this->hybridauth;
    }
    public function setHybridAuth($hybridauth){
        $this->hybridauth=$hybridauth;
        return $this;
    }

    private function setAccounts($accounts){
        $ids = array();
        foreach($accounts as $a){
            $ids[] = $a->getId();
        }
        $this->setSession('sessionAccountKey',$ids);
    }

    private getSession($key,$default=null){
        $translatedKey = $this->getConfig($key,'MultiAuth-'.$key);
        $value = HybridAuth::storage()
            ->get($translatedKey);
        if( $value === null){
            return $default;
        }
        return $value;
    }

    private setSession($key,$value){
        HybridAuth::storage()
            ->get($key,$value);
        return $this;
    }
    private callFunctionInConfigs($functionName, $params){
        //!TODO make a call on the function.
        $function = $this->getConfig(
            $functionName,
            function (){
                return null;
            }
        );
        if( !is_callable($function) ){
            //!TODO Exception
        }
        return call_account_func($function) 
    }

}

?>
