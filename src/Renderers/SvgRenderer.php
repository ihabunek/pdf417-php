<?php

namespace BigFish\PDF417\Renderers;

use BigFish\PDF417\BarcodeData;

use DOMImplementation;
use DOMElement;

class SvgRenderer extends AbstractRenderer
{
    /**
     * {@inheritdoc}
     */
    protected $options = [
        'scale' => 3,
        'ratio' => 3,
        'color' => "#000",
        'description' => null,
    ];

    /**
     * {@inheritdoc}
     */
    public function validateOptions()
    {
        $errors = [];

        $scale = $this->options['scale'];
        if (!is_numeric($scale) || $scale < 1 || $scale > 20) {
            $errors[] = "Invalid option \"scale\": \"$scale\". Expected an integer between 1 and 20.";
        }

        $ratio = $this->options['ratio'];
        if (!is_numeric($ratio) || $ratio < 1 || $ratio > 10) {
            $errors[] = "Invalid option \"ratio\": \"$ratio\". Expected an integer between 1 and 10.";
        }

        return $errors;
    }

    /**
     * {@inheritdoc}
     */
    public function getContentType()
    {
        return "image/svg+xml";
    }

    /**
     * {@inheritdoc}
     */
    public function render(BarcodeData $data)
    {
        $pixelGrid = $data->getPixelGrid();
        $height = count($pixelGrid);
        $width = count($pixelGrid[0]);

        $options = $this->options;

        // Apply scaling & aspect ratio
        $scaleX = $options['scale'];
        $scaleY = $options['scale'] * $options['ratio'];

        $width *= $scaleX;
        $height *= $scaleY;

        $doc = $this->createDocument();

        // Root document
        $svg = $doc->createElement("svg");
        $svg->setAttribute("height", $height);
        $svg->setAttribute("width", $width);
        $svg->setAttribute("version", "1.1");
        $svg->setAttribute("xmlns", "http://www.w3.org/2000/svg");

        // Add description node if defined
        $desc = $options['description'];
        if (!empty($desc)) {
            $svg->appendChild(
                $doc->createElement("description", $desc)
            );
        }

        // Create the group
        $group = $doc->createElement("g");
        $group->setAttribute('id', 'barcode');
        $group->setAttribute('fill', $options['color']);
        $group->setAttribute('stroke', 'none');

        // Add barcode elements to group
        foreach ($pixelGrid as $y => $row) {
            foreach ($row as $x => $item) {
                if ($item === false) {
                    continue;
                }

                $rect = $doc->createElement('rect');
                $rect->setAttribute("x", $x * $scaleX);
                $rect->setAttribute("y", $y * $scaleY);
                $rect->setAttribute("width", $scaleX);
                $rect->setAttribute("height", $scaleY);

                $group->appendChild($rect);
            }
        }

        $svg->appendChild($group);
        $doc->appendChild($svg);

        return $doc->saveXML();
    }

    /** Creates a DOMDocument for SVG. */
    protected function createDocument()
    {
        $impl = new DOMImplementation();

        $docType = $impl->createDocumentType(
            "svg",
            "-//W3C//DTD SVG 1.1//EN",
            "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd"
        );

        $doc = $impl->createDocument(null, null, $docType);
        $doc->formatOutput = true;

        return $doc;
    }
}
