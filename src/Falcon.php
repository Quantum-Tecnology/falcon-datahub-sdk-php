<?php

declare(strict_types = 1);

namespace QuantumTecnology\FalconDataHub;

use QuantumTecnology\FalconDataHub\Exceptions\FalconException;
use QuantumTecnology\FalconDataHub\Resources\AccessSessions\AccessSessionResource;
use QuantumTecnology\FalconDataHub\Resources\ApiKeys\ApiKeyResource;
use QuantumTecnology\FalconDataHub\Resources\Auth\AuthResource;
use QuantumTecnology\FalconDataHub\Resources\Brasil\BrasilResource;
use QuantumTecnology\FalconDataHub\Resources\CreditCard\CreditCardResource;
use QuantumTecnology\FalconDataHub\Resources\Delivery\DeliveryResource;
use QuantumTecnology\FalconDataHub\Resources\Finance\BcbResource;
use QuantumTecnology\FalconDataHub\Resources\Fipe\FipeResource;
use QuantumTecnology\FalconDataHub\Resources\Fiscal\FiscalResource;
use QuantumTecnology\FalconDataHub\Resources\Location\CityResource;
use QuantumTecnology\FalconDataHub\Resources\Location\StateResource;
use QuantumTecnology\FalconDataHub\Resources\Lookup\ActionResource;
use QuantumTecnology\FalconDataHub\Resources\Lookup\CepResource;
use QuantumTecnology\FalconDataHub\Resources\Lookup\CnaeResource;
use QuantumTecnology\FalconDataHub\Resources\Lookup\CnpjResource;
use QuantumTecnology\FalconDataHub\Resources\Lookup\IpResource;
use QuantumTecnology\FalconDataHub\Resources\Lookup\NcmResource;
use QuantumTecnology\FalconDataHub\Resources\Lookup\VehicleResource;
use QuantumTecnology\FalconDataHub\Resources\Plans\PlanResource;
use QuantumTecnology\FalconDataHub\Resources\Products\ProductResource;
use QuantumTecnology\FalconDataHub\Resources\Subscriptions\SubscriptionResource;
use QuantumTecnology\FalconDataHub\Resources\Usage\UsageResource;
use QuantumTecnology\FalconDataHub\Resources\Validation\ValidateResource;
use QuantumTecnology\FalconDataHub\Resources\Xml\XmlResource;

/**
 * Static facade for the Falcon DataHub SDK.
 *
 * Usage:
 *   Falcon::configure(new FalconConfig(baseUrl: '...', token: '...'));
 *   $result = Falcon::cep()->search('13472360');
 */
final class Falcon
{
    private static ?FalconClient $instance = null;

    public static function configure(FalconConfig $config): void
    {
        self::$instance = new FalconClient($config);
    }

    public static function client(): FalconClient
    {
        if (self::$instance === null) {
            throw new FalconException(
                'Falcon SDK not configured. Call Falcon::configure(new FalconConfig(...)) first.',
            );
        }

        return self::$instance;
    }

    public static function auth(): AuthResource
    {
        return self::client()->auth();
    }

    public static function cep(): CepResource
    {
        return self::client()->cep();
    }

    public static function cnpj(): CnpjResource
    {
        return self::client()->cnpj();
    }

    public static function cnae(): CnaeResource
    {
        return self::client()->cnae();
    }

    public static function ncm(): NcmResource
    {
        return self::client()->ncm();
    }

    public static function vehicle(): VehicleResource
    {
        return self::client()->vehicle();
    }

    public static function action(): ActionResource
    {
        return self::client()->action();
    }

    public static function ip(): IpResource
    {
        return self::client()->ip();
    }

    public static function cities(): CityResource
    {
        return self::client()->cities();
    }

    public static function states(): StateResource
    {
        return self::client()->states();
    }

    public static function validate(): ValidateResource
    {
        return self::client()->validate();
    }

    public static function brasil(): BrasilResource
    {
        return self::client()->brasil();
    }

    public static function bcb(): BcbResource
    {
        return self::client()->bcb();
    }

    public static function fipe(): FipeResource
    {
        return self::client()->fipe();
    }

    public static function fiscal(): FiscalResource
    {
        return self::client()->fiscal();
    }

    public static function products(): ProductResource
    {
        return self::client()->products();
    }

    public static function delivery(): DeliveryResource
    {
        return self::client()->delivery();
    }

    public static function xml(): XmlResource
    {
        return self::client()->xml();
    }

    public static function creditCards(): CreditCardResource
    {
        return self::client()->creditCards();
    }

    public static function plans(): PlanResource
    {
        return self::client()->plans();
    }

    public static function subscriptions(): SubscriptionResource
    {
        return self::client()->subscriptions();
    }

    public static function usage(): UsageResource
    {
        return self::client()->usage();
    }

    public static function apiKeys(): ApiKeyResource
    {
        return self::client()->apiKeys();
    }

    public static function accessSessions(): AccessSessionResource
    {
        return self::client()->accessSessions();
    }

    /**
     * Reset the static instance (useful for testing).
     */
    public static function reset(): void
    {
        self::$instance = null;
    }
}
