<?php declare(strict_types = 1);
namespace LibertAPI\Tests\Units\Tools\Libraries;

use LibertAPI\Utilisateur\UtilisateurEntite;
use Psr\Http\Message\ResponseInterface as IResponse;

/**
 * Classe de base des tests sur les contrôleurs REST
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.4
 */
abstract class ARestController extends AController
{
    /**
     * @var UtilisateurEntite Standardisation d'un rôle responsable
     */
    protected $currentResponsable;

    /**
     * @var UtilisateurEntite Standardisation d'un rôle employé
     */
    protected $currentEmploye;

    /**
     * @var UtilisateurEntite Standardisation d'un rôle admin
     */
    protected $currentAdmin;

    /*************************************************
     * GET
     *************************************************/

    /**
     * Teste la méthode get d'un détail trouvé
     */
    public function testGetOneFound()
    {
        $this->repository->getMockController()->getOne = $this->entite;
        $this->newTestedInstance($this->repository, $this->router, $this->currentEmploye);

        $response = $this->getOne();
        $data = $this->getJsonDecoded($response->getBody());

        $this->integer($response->getStatusCode())->isIdenticalTo(200);
        $this->array($data)
            ->integer['code']->isIdenticalTo(200)
            ->string['status']->isIdenticalTo('success')
            ->array['data']->isNotEmpty()
        ;
    }

    /**
     * Teste la méthode get d'un détail non trouvé
     */
    public function testGetOneNotFound()
    {
        $this->repository->getMockController()->getOne = function () {
            throw new \DomainException('');
        };
        $this->newTestedInstance($this->repository, $this->router, $this->currentEmploye);

        $response = $this->getOne();

        $this->assertFail($response, 404);
    }

    /**
     * Teste le fallback de la méthode get d'un détail
     */
    public function testGetOneFallback()
    {
        $this->repository->getMockController()->getOne = function () {
            throw new \Exception('');
        };
        $this->newTestedInstance($this->repository, $this->router, $this->currentEmploye);

        $response = $this->getOne();
        $response = $this->assertError($response);
    }

    abstract protected function getOne() : IResponse;

    /**
     * Teste la méthode get d'une liste trouvée
     */
    public function testGetListFound()
    {
        $this->request->getMockController()->getQueryParams = [];
        $this->repository->getMockController()->getList = [$this->entite,];
        $this->newTestedInstance($this->repository, $this->router, $this->currentAdmin);

        $response = $this->getList();
        $data = $this->getJsonDecoded($response->getBody());

        $this->integer($response->getStatusCode())->isIdenticalTo(200);
        $this->array($data)
            ->integer['code']->isIdenticalTo(200)
            ->string['status']->isIdenticalTo('success')
            //->array['data']->hasSize(1) // TODO: l'asserter atoum en sucre syntaxique est buggé, faire un ticket
        ;
        $this->boolean(empty($data['data']))->isFalse();
    }

    /**
     * Teste la méthode get d'une liste vide
     */
    public function testGetListNoContent()
    {
        $this->request->getMockController()->getQueryParams = [];
        $this->repository->getMockController()->getList = function () {
            throw new \UnexpectedValueException('');
        };
        $this->newTestedInstance($this->repository, $this->router, $this->currentAdmin);

        $response = $this->getList();

        $this->assertSuccessEmpty($response);
    }

    /**
     * Teste le fallback de la méthode get d'une liste
     */
    public function testGetListFallback()
    {
        $this->request->getMockController()->getQueryParams = [];
        $this->repository->getMockController()->getList = function () {
            throw new \Exception('');
        };
        $this->newTestedInstance($this->repository, $this->router, $this->currentAdmin);

        $response = $this->getList();
        $this->assertError($response);
    }

    abstract protected function getList() : IResponse;

    abstract protected function getEntiteContent() : array;
}
