<?php
namespace CleaRest\Metadata\Parsers;

use CleaRest\Metadata\Annotation;
use CleaRest\Metadata\ConsoleTool;
use CleaRest\Util\ArrayFunctions;
use zpt\anno\AnnotationParser;

/**
 * Parses the doc comment and extracts its annotations and text
 */
class DocCommentParser
{

    /**
     * Returns annotations defined in a doc comment
     * @param string $docComment
     * @param string $origin where the doc comment comes from
     * @return Annotation[]
     */
	public static function getAnnotations($docComment, $origin = null)
	{
		$annotations = [];
		$extractedAnnotations = AnnotationParser::getAnnotations($docComment);
        foreach ($extractedAnnotations as $name => $occurrences) {
            if (is_scalar($occurrences) || ArrayFunctions::isAssociative($occurrences)) {
                $occurrences = [$occurrences];
            }
            foreach ((array)$occurrences as $parameters) {
                if (is_string($parameters)) {
                    $parameters = preg_split('/[ ]+/', $parameters);
                } elseif (is_scalar($parameters)) {
                    $parameters = [$parameters];
                }
                try {
                    $annotations[] = Annotation::create($name, $parameters);
                } catch (\Exception $ex) {
                    ConsoleTool::addWarning($ex->getMessage() .($origin !== null ? " in $origin " : ""));
                }
            }
        }
        return $annotations;
	}

    /**
     * Extracts the doc comment description (without annotations)
     * @param string $docComment
     * @return string
     */
	public static function getText($docComment)
	{
		// @ToDo: Use a nice Regex instead

		$dirtyLines = explode("\n", $docComment);
		$cleanLines = [];
		foreach ($dirtyLines as $line) {
			do {
				$length = strlen($line);
				$startsWith = substr($line, 0, 1);
				if (in_array($startsWith, ['/', '*'])) {
					$line = substr($line, 1);
				}
				$line = trim($line);
			} while (strlen($line) < $length);

			if ($startsWith != '@' && strlen($line) > 0) {
				$cleanLines[] = $line;

			}
		}

		return join("\n", $cleanLines);
	}
}
