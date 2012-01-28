<?php
namespace MultiAuth; 

/**
 * Your user must implements interface in order to use MultiAuth..
 *
 * @author Luc Boissaye
 * @version 1.0
 */
interface User{
    /**
     * Please return a unique id for your user
     * @return a unique id  for this user.
     * @author Luc Boissaye
     * @since 1.0
     */
    public function getId();
}
?>
