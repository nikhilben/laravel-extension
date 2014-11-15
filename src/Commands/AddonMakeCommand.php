<?php namespace Jumilla\LaravelExtension\Commands;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Jumilla\LaravelExtension\AddonManager;

/**
* Modules console commands
* @author Fumio Furukawa <fumio.furukawa@gmail.com>
*/
class AddonMakeCommand extends AbstractCommand {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'addon:make';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Make addon.';

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		// load laravel services
		$files = $this->laravel['files'];
		$translator = $this->laravel['translator'];

		// load command arguments
		$addonName = $this->argument('name');
		$namespace = $this->option('namespace');
		if (empty($namespace))
			$namespace = ucfirst(studly_case($addonName));
		if ($this->option('no-namespace'))
			$namespace = '';

		$namespacePrefix = $namespace ? $namespace.'\\' : '';

		// output spec
		$this->line('== Making Addon Specs ==');
		$this->line(sprintf('Directory name: "%s"', $addonName));
		$this->line(sprintf('PHP namespace: "%s"', $namespace));

		$addonsDirectory = AddonManager::path();
//		$templateDirectory = dirname(dirname(__DIR__)).'/templates/addon';

		// make addons/
		if (!$files->exists($addonsDirectory))
			$files->makeDirectory($addonsDirectory);

		$basePath = $this->basePath = $addonsDirectory.'/'.$addonName;

		if ($files->exists($basePath)) {
			$this->error(sprintf('Error: directory "%s" already exists.', $basePath));
			return;
		}

		$files->makeDirectory($basePath);

		$this->makeDirectories([
			'config',
			'specs',
			'classes',
			'classes/Console',
			'classes/Console/Commands',
			'classes/Http',
			'classes/Http/Controllers',
			'classes/Http/Middleware',
			'classes/Http/Requests',
			'classes/Providers',
			'classes/Services',
			'database',
			'database/migrations',
			'database/seeds',
			'resources',
			'resources/assets',
			'resources/lang',
			'resources/lang/en',
			'resources/views',
		]);
		if ($translator->getLocale() !== 'en') {
			$this->makeDirectories([
				'resources/lang/'.$translator->getLocale(),
			]);
		}

/*
		$this->makeComposerJson($namespace, [
			'controllers',
			'migrations',
			'models',
		]);
*/

		$this->makePhpConfig('config/config.php', [
			'sample_title' => 'Addon: '.$addonName,
		]);
		$this->makePhpConfig('config/addon.php', [
			'version' => 5,
			'namespace' => $namespace,
			'directories' => [
				'classes',
			],
			'paths' => [
				'assets' => 'resources/assets',
				'lang' => 'resources/lang',
				'views' => 'resources/views',
				'migrations' => 'database/migrations',
				'seeds' => 'database/seeds',
			],
			'providers' => [
				'Providers\AddonServiceProvider',
				'Providers\RouteServiceProvider',
			],
			'commands' => [
			],
			'middlewares' => [
			],
			'includes_global_aliases' => true,
			'aliases' => [
			],
		]);

		// controllers/Http/Controllers/BaseController.php
		$source = <<<SRC

use Illuminate\Routing\Controller;

class BaseController extends Controller {

}
SRC;
		$this->makePhpSource('classes/Http/Controllers/BaseController.php', $source, $namespace.'\\Http\\Controllers');

		// controllers/Http/Controllers/SampleController.php
		$source = <<<SRC
class SampleController extends BaseController {

	public function index() {
		return View::make('{$addonName}::sample');
	}

}
SRC;
		$this->makePhpSource('classes/Http/Controllers/SampleController.php', $source, $namespace.'\\Http\\Controllers');

		// controllers/Providers/AddonServiceProvider.php
		$source = <<<SRC

class AddonServiceProvider extends \Illuminate\Support\ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected \$defer = false;

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
	}

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return [];
	}

}
SRC;
		$this->makePhpSource('classes/Providers/AddonServiceProvider.php', $source, $namespace.'\\Providers');

		// controllers/Providers/ServiceProvider.php
		$source = <<<SRC

use Illuminate\Routing\Router;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider {

	/**
	 * All of the application's route middleware keys.
	 *
	 * @var array
	 */
	protected \$middleware = [
	];

	protected \$scan = [
	];
//	protected \$scanWhenLocal = true;

	/**
	 * Called before routes are registered.
	 *
	 * Register any model bindings or pattern based filters.
	 *
	 * @return void
	 */
	public function before(/* add any injection */)
	{
	}

	/**
	 * Define the routes for the addon.
	 *
	 * @param  \Illuminate\Routing\Router  \$router  (injection)
	 * @return void
	 */
	public function map(Router \$router)
	{
	}

}
SRC;
		$this->makePhpSource('classes/Providers/RouteServiceProvider.php', $source, $namespace.'\\Providers');

		// views/sample.blade.php
		$source = <<<SRC
<h1>{{ Config::get('{$addonName}::sample_title') }}</h1>
SRC;
		$this->makeTextFile('resources/views/sample.blade.php', $source);

		// routes.php
		$source = <<<SRC
Route::get('addons/{$addonName}', ['uses' => '{$namespacePrefix}SampleController@index']);
SRC;
		$this->makePhpSource('classes/Http/routes.php', $source);

		$this->info('Addon Generated');
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return [
			['name', InputArgument::REQUIRED, 'Addon name.'],
		];
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return [
			['namespace', null, InputOption::VALUE_OPTIONAL, 'Addon namespace.', null],
			['no-namespace', null, InputOption::VALUE_NONE, 'Addon namespace nothing.', null],
		];
	}

}