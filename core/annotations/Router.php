<?php
namespace Albin\core\annotations;
use Doctrine\Common\Annotations\Annotation\Target;
use Doctrine\Common\Annotations\Annotation;
/**
 * Class Router
 *
 *
 * @Annotation
 * @Target(["METHOD"])
 */
final class Router {

    /**
     * @var
     * @Annotation\Required()
     */
    public $route;

    /**
     * @var
     * @Annotation\Required()
     * @Annotation\Enum({"GET", "POST", "PUT", "DELETE"})
     */
    public $method;


}