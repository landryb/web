<?php declare(strict_types = 1);
namespace LibertAPI\Tools\Controllers;

use Psr\Http\Message\ServerRequestInterface as IRequest;
use Psr\Http\Message\ResponseInterface as IResponse;
use \Slim\Interfaces\RouterInterface as IRouter;
use LibertAPI\Utilisateur;

/**
 * Contrôleur d'utilisateurs
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.4
 * @see \LibertAPI\Utilisateur\UtilisateurController
 *
 * Ne devrait être contacté que par le routeur
 * Ne devrait contacter que le Utilisateur\Repository
 */
final class UtilisateurController extends \LibertAPI\Tools\Libraries\AController
{
    public function __construct(Utilisateur\UtilisateurRepository $repository, IRouter $router)
    {
        parent::__construct($repository, $router);
    }

    /*************************************************
     * GET
     *************************************************/

    /* Mettre le niveau de droits autorisés
        && ce que ces droits permettre de voir (l'utilisateur peut-il voir HR / admin ?)
     */

     /**
      * {@inheritDoc}
      */
    public function get(IRequest $request, IResponse $response, array $arguments) : IResponse
    {
        if (!isset($arguments['utilisateurId'])) {
            return $this->getList($request, $response);
        }

        return $this->getOne($response, (int) $arguments['utilisateurId']);
    }

    /**
     * Retourne un élément unique
     *
     * @param IResponse $response Réponse Http
     * @param int $id ID de l'élément
     *
     * @return IResponse, 404 si l'élément n'est pas trouvé, 200 sinon
     */
    private function getOne(IResponse $response, $id)
    {
        try {
            $utilisateur = $this->repository->getOne($id);
        } catch (\DomainException $e) {
            return $this->getResponseNotFound($response, 'Element « utilisateurs#' . $id . ' » is not a valid resource');
        } catch (\Exception $e) {
            return $this->getResponseError($response, $e);
        }

        return $this->getResponseSuccess(
            $response,
            $this->buildData($utilisateur),
            200
        );
    }

    /**
     * Retourne un tableau d'utilisateurs
     *
     * @param IRequest $request Requête Http
     * @param IResponse $response Réponse Http
     *
     * @return IResponse
     * @throws \Exception en cas d'erreur inconnue (fallback, ne doit pas arriver)
     */
    private function getList(IRequest $request, IResponse $response)
    {
        try {
            $utilisateurs = $this->repository->getList(
                $request->getQueryParams()
            );
        } catch (\UnexpectedValueException $e) {
            return $this->getResponseNoContent($response);
        } catch (\Exception $e) {
            return $this->getResponseError($response, $e);
        }
        $entites = array_map([$this, 'buildData'], $utilisateurs);

        return $this->getResponseSuccess($response, $entites, 200);
    }

    /**
     * Construit le « data » du json
     *
     * @param Utilisateur\UtilisateurEntite $entite Utilisateur
     *
     * @return array
     */
    private function buildData(Utilisateur\UtilisateurEntite $entite)
    {
        return [
            'id' => $entite->getId(),
            'login' => $entite->getLogin(),
            'nom' => $entite->getNom(),
            'date_inscription' => $entite->getDateInscription(),
            // mettre le lien du planning associé, sous un offset formalisé
        ];
    }
}
