<?php

namespace corsica\framework\utils;

use Firebase\JWT\JWT;
use corsica\framework\config\Config;
use DateTimeImmutable;
use Firebase\JWT\Key;
use Exception;

/**
 * Para la sesión con Token
 */
class Token
{
    private const DURACION = '+600 minute';
    /**
     * Devuelve un  objeto env en caso de éxito, sino arroja una excepción
     */
    public function getEnv($token)
    {
        $secretKey = Config::getSecretKey();
        //echo "obteniendo clave ", PHP_EOL;
        //echo "Token = ", $token, PHP_EOL;
        //echo "secretKey = ", $secretKey, PHP_EOL;
        //  $token = JWT::decode($token, new key ($secretKey,'HS256')$secretKey, ['HS512']);
        $token = JWT::decode($token, new Key($secretKey, 'HS512'));
        //echo "nuevo token = ", json_encode($token), PHP_EOL;

        //throw new Exception("caga");
        $env = array("env" => $token->env, "idusuario" => $token->idusuario, "username" => $token->username, "idperfil" => $token->idperfil);
        $env = (object) $env;
        return $env;
    }

    /**
     * Entrega un TOKEN
     */
    public function getToken($idusuario, $username, $idperfil, $env)
    {


        $secretKey = Config::getSecretKey();
        $issuedAt = new DateTimeImmutable();
        $expire = $issuedAt->modify($this::DURACION)->getTimestamp(); // Add 60 seconds
        $serverName = "www.yogi.cl";
        $data = [
            'iat' => $issuedAt->getTimestamp(),
            // Issued at: time when the token was generated
            'iss' => $serverName,
            // Issuer
            'nbf' => $issuedAt->getTimestamp(),
            // Not before
            'exp' => $expire,
            // Expire
            'idusuario' => $idusuario,
            // User id
            'username' => $username,
            // User name
            'idperfil' => $idperfil,
            // Profile id
            'env' => $env, // User name
        ];
        //print_r($data);
        $token = JWT::encode(
            $data,
            $secretKey,
            'HS512'
        );
        return $token;
    }
}