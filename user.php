<?php
namespace MultiAuth; 

/**
 * Your user must implements interface in order to use MultiAuth..
 *
 * @author Luc Boissaye
 * @version 1.0
 */
interface user{
    /**
     * This function will be called when a user add a new provider to his account.
     * Note : You may check in this function if another user already have this provider.
     * @param $providerId The name of the provider
     * @param $userId The id for this provider
     * @param $datas Datas that will be used for the api.
     * @return void
     * @author Luc Boissaye
     * @since 1.0
     */
    public function addProvider(string $providerId,string $UserId,string $datas);
    /**
     * Please return a unique id for your user
     * @return a unique id  for this user.
     * @author Luc Boissaye
     * @since 1.0
     */
    public function getId();
}
?>
