<?php

namespace Nicolaskuster\ApiDoc\Generators;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Nicolaskuster\ApiDoc\Models\Route;

class MDGenerator implements Generator
{
    const FORMAT_NORMAL = 0;
    const FORMAT_H1 = 1;
    const FORMAT_H2 = 2;
    const FORMAT_H3 = 3;
    const FORMAT_H4 = 4;
    const FORMAT_CODE = 5;

    /**
     * The output path for the documentation
     *
     * @var string
     */
    protected $outputPath;


    /**
     * MDGenerator constructor.
     *
     * @param array $config
     */
    public function __construct($config)
    {
        $this->outputPath = $config['outputPath'];
    }

    /**
     * {@inheritdoc}
     */
    public function generate(Collection $routes): void
    {
        //Clear File
        File::put($this->outputPath, '');

        $this->appendBlock('API Documentation', self::FORMAT_H1);

        foreach ($routes as $route) {
            $this->docRoute($route);
        }
    }

    /**
     * Document the provided route
     *
     * @param Route $route
     */
    protected function docRoute(Route $route)
    {
        $this->appendBlock(
            implode(' | ', $route->getMethods()) . " " . $route->getUri(),
            self::FORMAT_H2
        );

        if ($validationRules = $route->getValidationRules()) {
            $validationRulesMD = '';
            foreach ($validationRules as $validationRule) {
                $validationRulesMD .= $validationRule->getAttributeName() . " => " . implode(' | ', $validationRule->getRules()) . "\n";
            }

            $this->appendBlock(
                $validationRulesMD,
                self::FORMAT_CODE
            );
        }
    }

    /**
     * Append block to the documentation
     *
     * @param string $content
     * @param int $format
     */
    protected function appendBlock($content, $format = self::FORMAT_NORMAL)
    {
        switch ($format) {
            case self::FORMAT_H1:
                $content = "#" . $content;
                break;
            case self::FORMAT_H2:
                $content = "##" . $content;
                break;
            case self::FORMAT_H3:
                $content = "###" . $content;
                break;
            case self::FORMAT_H4:
                $content = "####" . $content;
                break;
            case self::FORMAT_CODE:
                $content = "```\n" . $content . "```";
                break;
        }
        File::append($this->outputPath, $content . "\n");
    }
}