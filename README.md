Intracto Fas OpenId bundle
==========================
With this bundle, users of your application will be able to login into the application using FAS (Federal Authentication Service) using OpenId.

Installation
============
Applications that use Symfony Flex
----------------------------------
Open a command console, enter your project directory and execute:

```console
$ composer require intracto/fas-open-id-bundle
```

Applications that don't use Symfony Flex
----------------------------------------

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require intracto/fas-open-id-bundle
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project:

```php
// config/bundles.php

return [
    // ...
    <vendor>\<bundle-name>\<bundle-long-name>::class => ['all' => true],
];
```

### Step 3: register bundle routing
Load the bundle's routing inside your application
```yaml
intracto_fas_open_id:
    resource: "@IntractoFasOpenIdBundle/Resources/config/routing.xml"
```
### Step 4: Configuration
#### Bundle configuration
(If not done by Flex, create a intracto_fas_open_id.yaml file in your config/packages folder).

Config parameters needed to get this bundle working:
* `client_id`: the client ID of your registered application
* `client_secret`:  the client secret of your registerd application
* `scope`: list of scopes that will be used by this application. Possible values are profile, egovnrn, certificateInfo, citizen, enterprise and roles. The role openid will automatically be used
* `auth_path`: the route name where the `FasOpenIdAuthenticator` will check to authenticate the user. The default value for this parameter is `intracto_fas_open_id.auth`
* `target_path`: the route name where the user will be redirected to on successful authentication
* `login_path`: the route name where the user will be redirected to when he has to login
* `user_class`: FQN of your user class, this is optional. Make sure your user extends the User class of this bundle

#### Firewall configuration
Then, you have to tell the firewall(s) of your application which authentictor should be used. Under the guard parameter of your firewall(s), you have to append the `intracto.fas_open_id.authenticator` to the authenticators parameter.
```yaml
security:
    ...
    my_firewall:
        ...
        guard:
            ...
            authenticators:
                - intracto.fas_open_id.authenticator

```

If you want to log out from FAS, add the `intracto.fas_open_id.logout_handler` to your firewall logout handlers.
