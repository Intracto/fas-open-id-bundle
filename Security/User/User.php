<?php

namespace Intracto\FasOpenIdBundle\Security\User;

use Symfony\Component\Security\Core\User\UserInterface;

class User implements UserInterface
{
    /**
     * @var string
     */
    private $nationalInsuranceNumber;

    /**
     * @var string
     */
    private $firstName;

    /**
     * @var string
     */
    private $lastName;

    /**
     * @var string
     */
    private $prefLanguage;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $certIssuer;

    /**
     * @var string
     */
    private $certSubject;

    /**
     * @var string
     */
    private $certSerialNumber;

    /**
     * @var string
     */
    private $certCn;

    /**
     * @var string
     */
    private $certGivenName;

    /**
     * @var string
     */
    private $certSn;

    /**
     * @var string
     */
    private $certMail;

    /**
     * @var array
     */
    private $fasRoles;

    public function getNationalInsuranceNumber(): string
    {
        return $this->nationalInsuranceNumber;
    }

    public function setNationalInsuranceNumber(string $nationalInsuranceNumber): self
    {
        $this->nationalInsuranceNumber = $nationalInsuranceNumber;

        return $this;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getPrefLanguage(): string
    {
        return $this->prefLanguage;
    }

    public function setPrefLanguage(string $prefLanguage): self
    {
        $this->prefLanguage = $prefLanguage;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getCertIssuer(): string
    {
        return $this->certIssuer;
    }

    public function setCertIssuer(string $certIssuer): self
    {
        $this->certIssuer = $certIssuer;

        return $this;
    }

    public function getCertSubject(): string
    {
        return $this->certSubject;
    }

    public function setCertSubject(string $certSubject): self
    {
        $this->certSubject = $certSubject;

        return $this;
    }

    public function getCertSerialNumber(): string
    {
        return $this->certSerialNumber;
    }

    public function setCertSerialNumber(string $certSerialNumber): self
    {
        $this->certSerialNumber = $certSerialNumber;

        return $this;
    }

    public function getCertCn(): string
    {
        return $this->certCn;
    }

    public function setCertCn(string $certCn): self
    {
        $this->certCn = $certCn;

        return $this;
    }

    public function getCertGivenName(): string
    {
        return $this->certGivenName;
    }

    public function setCertGivenName(string $certGivenName): self
    {
        $this->certGivenName = $certGivenName;

        return $this;
    }

    public function getCertSn(): string
    {
        return $this->certSn;
    }

    public function setCertSn(string $certSn): self
    {
        $this->certSn = $certSn;

        return $this;
    }

    public function getCertMail(): string
    {
        return $this->certMail;
    }

    public function setCertMail(string $certMail): self
    {
        $this->certMail = $certMail;

        return $this;
    }

    public function getFasRoles(): array
    {
        return $this->fasRoles;
    }

    public function setFasRoles(array $fasRoles): self
    {
        $this->fasRoles = $fasRoles;

        return $this;
    }
    /**
     * @inheritDoc
     */
    public function getRoles()
    {
        return ['ROLE_USER'];
    }

    /**
     * @inheritDoc
     */
    public function getPassword(): string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getUsername(): string
    {
        return $this->nationalInsuranceNumber;
    }

    /**
     * @inheritDoc
     */
    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }
}
