<?php

namespace Intracto\FasOpenIdBundle\Util;

use Intracto\FasOpenIdBundle\Service\FasOpenIdOAuthClient;
use Symfony\Component\Security\Core\User\UserInterface;

class UserInfoMapper
{
    /**
     * Use this function to map the FAS user information to a user object
     * @param UserInterface $user
     * @param               $userInfo
     * @param array         $scope
     *
     * @return UserInterface
     */
    public static function mapUserInfoToUserObject(UserInterface $user, $userInfo, array $scope): UserInterface
    {
        if (in_array(FasOpenIdOAuthClient::SCOPE__EGOVNRN, $scope, true)) {
            $user->setNationalInsuranceNumber($userInfo->egovNRN ?? null);
        }

        if (in_array(FasOpenIdOAuthClient::SCOPE_PROFILE, $scope, true)) {
            $user->setFirstName($userInfo->givenName ?? null);
            $user->setLastName($userInfo->surname ?? null);
            $user->setPrefLanguage($userInfo->prefLanguage ?? null);
            $user->setEmail($userInfo->mail ?? null);
        }

        if (in_array(FasOpenIdOAuthClient::SCOPE_CERTIFICATE_INFO, $scope, true)) {
            $user->setCertIssuer($userInfo->cert_issuer ?? null);
            $user->setCertSubject($userInfo->cert_subject ?? null);
            $user->setCertSerialNumber($userInfo->cert_serialnumber ?? null);
            $user->setCertCn($userInfo->cert_cn ?? null);
            $user->setCertGivenName($userInfo->cert_givenname ?? null);
            $user->setCertSn($userInfo->cert_sn ?? null);
            $user->setCertMail($userInfo->cert_mail ?? null);
        }

        if (in_array(FasOpenIdOAuthClient::SCOPE_ROLES, $scope, true)) {
            $user->setFasRoles($userInfo->roles ?? null);
        }

        return $user;
    }
}
