<?php

namespace App;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\CommonMark\Node\Inline\Image;
use League\CommonMark\MarkdownConverter;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;

class StayMarkdown
{
    public function render(?string $text): string
    {
        $environment = new Environment([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
            'max_nesting_level' => 20,
            'renderer' => ['soft_break' => "<br>\n"],
        ]);
        $environment->addExtension(new CommonMarkCoreExtension);
        $environment->addRenderer(Image::class, new class implements NodeRendererInterface
        {
            public function render(Node $node, ChildNodeRendererInterface $childRenderer): string
            {
                return '';
            }
        }, 100);

        return (string) (new MarkdownConverter($environment))->convert($text ?? '');
    }
}
