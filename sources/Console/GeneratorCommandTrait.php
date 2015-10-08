<?php

namespace LaravelPlus\Extension\Console;

use Illuminate\Support\Str;
use LaravelPlus\Extension\Addons\AddonDirectory;
use LaravelPlus\Extension\Addons\Addon;
use UnexpectedValueException;

trait GeneratorCommandTrait
{
    /**
     * addon.
     *
     * @var \LaravelPlus\Extension\Addons\Addon
     */
    protected $addon;

    /**
     * Execute the console command.
     */
    public function fire()
    {
        $this->addon = $this->getAddon();

        return parent::fire();
    }

    protected function getAddon()
    {
        if ($addon = $this->option('addon')) {
            if (!AddonDirectory::exists($addon)) {
                throw new UnexpectedValueException("Addon '$addon' is not found.");
            }

            return Addon::create(AddonDirectory::path($addon));
        } else {
            return;
        }
    }

    /**
     * Parse the name and format according to the root namespace.
     *
     * @param string $name
     *
     * @return string
     */
    protected function parseName($name)
    {
        if ($this->addon) {
            $rootNamespace = $this->addon->phpNamespace();
        } else {
            $rootNamespace = $this->laravel->getNamespace();
        }

        if (Str::startsWith($name, $rootNamespace)) {
            return $name;
        }

        if (Str::contains($name, '/')) {
            $name = str_replace('/', '\\', $name);
        }

        return $this->parseName($this->getDefaultNamespace(trim($rootNamespace, '\\')).'\\'.$name);
    }

    /**
     * Get the destination class path.
     *
     * @param string $name
     *
     * @return string
     */
    protected function getPath($name)
    {
        if ($this->addon) {
            $name = str_replace($this->addon->phpNamespace(), '', $name);
            $path = str_replace('\\', '/', $name);

            return $this->getBasePath($this->addon).'/'.$path.'.php';
        } else {
            return parent::getPath($name);
        }
    }

    /**
     * Get the destination class base path.
     *
     * @param \LaravelPlus\Extension\Addons\Addon $addon
     *
     * @return string
     */
    protected function getBasePath(Addon $addon)
    {
        return $addon->path('classes');
    }

    /**
     * Load template.
     *
     * @param string $content
     * @param array  $arguments
     *
     * @return string
     */
    protected function template($content, array $arguments = [])
    {
        foreach ($arguments as $name => $value) {
            if (is_array($value)) {
                $value = implode(', ', $value);
            }
            $content = preg_replace('/\{\s*\$'.$name.'\s*\}/', $value, $content);
        }

        return $content;
    }
}