<?php

declare(strict_types = 1);

namespace QuantumTecnology\FalconDataHub\Resources\Fiscal;

use QuantumTecnology\FalconDataHub\Resources\AbstractResource;
use QuantumTecnology\FalconDataHub\Response\ApiResponse;

final class FiscalResource extends AbstractResource
{
    public function cfopList(?string $filter = null): ApiResponse
    {
        $query = [];

        if ($filter !== null) {
            $query['filter'] = $filter;
        }

        return $this->get('private/v1/fiscal/cfop', $query);
    }

    public function cfop(string $code): ApiResponse
    {
        return $this->get("private/v1/fiscal/cfop/{$code}");
    }

    public function cstList(string $type): ApiResponse
    {
        return $this->get("private/v1/fiscal/cst/{$type}");
    }

    public function cst(string $type, string $code): ApiResponse
    {
        return $this->get("private/v1/fiscal/cst/{$type}/{$code}");
    }

    /**
     * Lista a Lista de Serviço Nacional (cTribNac) — paginada, com busca por
     * descrição/código e filtro por item da LC 116.
     *
     * @param string|null $search termo (descrição ou código, ex.: "software", "08.02")
     * @param int|null    $item   item da LC 116 (ex.: 1 = informática)
     * @param int|null    $page   página (paginação do DataHub)
     */
    public function serviceCodesList(
        ?string $search = null,
        ?int $item = null,
        ?int $page = null,
    ): ApiResponse {
        $query = [];

        if ($search !== null && $search !== '') {
            $query['search'] = $search;
        }

        if ($item !== null) {
            $query['filter']['item'] = $item;
        }

        if ($page !== null) {
            $query['page'] = $page;
        }

        return $this->get('private/v1/service-codes', $query);
    }

    public function serviceCode(int $id): ApiResponse
    {
        return $this->get("private/v1/service-codes/{$id}");
    }

    /**
     * Lista o de-para do Código de Tributação Municipal (cTribMun) — paginado,
     * com busca e filtros por município (IBGE) e código nacional (cTribNac).
     *
     * O cTribMun NÃO é derivável do cTribNac: cada prefeitura administra sua
     * própria lista. Para resolver o cTribMun de uma emissão, filtre por
     * municipio_ibge + c_trib_nac (retorna a linha única daquele município).
     *
     * @param string|null $municipioIbge IBGE de 7 dígitos do município (ex.: "3501608")
     * @param string|null $cTribNac      código nacional de 6 dígitos (ex.: "080201")
     * @param string|null $search        termo (descrição, cTribNac ou cTribMun)
     * @param int|null    $page          página (paginação do DataHub)
     */
    public function municipalServiceCodesList(
        ?string $municipioIbge = null,
        ?string $cTribNac = null,
        ?string $search = null,
        ?int $page = null,
    ): ApiResponse {
        $query = [];

        if ($municipioIbge !== null && $municipioIbge !== '') {
            $query['filter']['municipio_ibge'] = $municipioIbge;
        }

        if ($cTribNac !== null && $cTribNac !== '') {
            $query['filter']['c_trib_nac'] = $cTribNac;
        }

        if ($search !== null && $search !== '') {
            $query['search'] = $search;
        }

        if ($page !== null) {
            $query['page'] = $page;
        }

        return $this->get('private/v1/municipal-service-codes', $query);
    }

    public function municipalServiceCode(int $id): ApiResponse
    {
        return $this->get("private/v1/municipal-service-codes/{$id}");
    }
}
