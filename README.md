When I started to use HybridAuth, I feel something was missing.

What if I want to have 2 twitter or 2 facebook account for my account ?

I would like to have more than one account, and be able to log in with only one provider.

The relationship between an account on your website and a Connection Provider should be N<->N

This is what I tried to do here.

To use this library, you just need to define 4 functions :

accountsFromProviderIdFunction
getAccountFunction
createAccountFunction
addProviderToAccount

For more details, please look for https://github.com/ombr/MultiAuth-Examples-Doctrine



This library can be integrated into every project by just implementing this 4 functions.

You can change the behavior, just by modifying the implementation of these 4 function.

Hope you like it !
