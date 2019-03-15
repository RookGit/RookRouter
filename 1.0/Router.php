<?

/** Example:
 *
 * Добавить роут с наличием get и post запроса
 * $ROUTER('test', 'url')->addRequest('GET&post');
 *
 * Добавить роут с наличием request запроса
 * $ROUTER('test', 'url')->addRequest('request');
 *
 * Добавить роут без проверки на наличие запроса
 * $ROUTER('test', 'url');
 *
 * Получить 0-вой роут из url с delimiter /
 * $ROUTER->getUrl(0);
 *
 * Получить массив роутов
 * $ROUTER->getUrl();
 */

interface RouterMethods
{

    // Получить роутеры url
    public function addUrlRoutes();

    // Получить роут
    public function getUrl($number);

    // Добавить роут
    public function add(string $rout, string $controller): Router;

    public function __invoke(string $rout, string $controller): Router;

    // Добавить группу роутов
    public function addGroup(array $params);

    // Добавить фильтр запроса (POST,GET..) для роутера
    public function addRequest(string $filter_request): Router;

    // Проверить - соответствует ли url[0] роуту
    public function checkRout();
}

Class Router implements RouterMethods
{

    // URL роуты
    private $url_routes = [];

    // Последний, добавленный роут
    private $last_rout;

    // Роуты
    private $routes = [];

    // Активный контроллер
    static $active_controller;


    function __construct()
    {
        $this->addUrlRoutes();
    }

    public function addUrlRoutes()
    {
        $path_info = $_SERVER['REDIRECT_URL'];
        $param = explode('/', $path_info);
        @array_shift($param);

        if (@$param[0] == 'index.php') @array_shift($param);
        if (count($param) == 0) $param = array('root');

        $this->url_routes = $param;

        return $this;
    }

// Вернуть активный контроллер
    static function getController()
    {
        return self::$active_controller;
    }

    public
    function __invoke(string $rout, string $controller): Router
    {
        return $this->add($rout, $controller);
    }

    public
    function add(string $rout, string $controller): Router
    {
        $this->last_rout = $rout;

        $this->routes[$rout] = [
            'controller' => $controller
        ];

        return $this;
    }

    public
    function addRequest(string $filter_request): Router
    {
        $filter_request = mb_strtolower($filter_request);

        switch ($filter_request) {
            case 'get':

                $this->routes[$this->last_rout] = [
                    'filter_request' => ['get']
                ];
                break;

            case 'post':
                $this->routes[$this->last_rout] = [
                    'filter_request' => ['post']
                ];
                break;

            case 'request':
                $this->routes[$this->last_rout] = [
                    'filter_request' => ['request']
                ];
                break;

            case 'post&get':
            case 'get&post':
                $this->routes[$this->last_rout] = [
                    'filter_request' => ['post', 'get']
                ];
                break;
        }

        return $this;

    }

    public
    function checkRout()
    {

        if (
            !empty($this->routes) &&
            is_array($this->routes) &&
            array_key_exists(
                $this->getUrl(0),
                $this->routes)
        ) {

            $rout = $this->routes[$this->getUrl(0)];


            if (!empty($rout['filter_request'])) {
                foreach ($rout['filter_request'] as $item) {
                    switch ($item) {
                        case 'get':
                            if (empty($_GET))
                                return false;
                            break;

                        case 'post':
                            if (empty($_POST))
                                return false;
                            break;

                        case 'request':
                            if (empty($_REQUEST))
                                return false;
                            break;
                    }
                }
            }


            self::$active_controller = $rout['controller'];

            return true;
        } else
            return false;

    }

    public
    function addGroup(array $params)
    {
        // TODO: Implement addGroup() method.
    }

    public function getUrl($number = null)
    {
        if ($number === null)
            return $this->url_routes;
        else
            return array_key_exists($number, $this->url_routes)
                ? $this->url_routes[$number] : false;
    }
}

$ROUTER = new Router();



