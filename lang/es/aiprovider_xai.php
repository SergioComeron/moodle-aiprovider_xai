<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Strings for component aiprovider_xai, language 'es'.
 *
 * @package    aiprovider_xai
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['action:explain_text:endpoint'] = 'Punto final de la API';
$string['action:explain_text:model'] = 'Modelo de IA';
$string['action:explain_text:model_help'] = 'El modelo utilizado para explicar el texto proporcionado.';
$string['action:explain_text:systeminstruction'] = 'Instrucción del sistema';
$string['action:explain_text:systeminstruction_default'] = 'Explica el texto del usuario. Devuelve la explicación solo en texto plano, en el mismo idioma que el texto de entrada. Sin markdown, saludos ni fórmulas de cortesía.';
$string['action:explain_text:systeminstruction_help'] = 'Esta instrucción se envía al modelo de IA junto con la petición del usuario. No se recomienda editar esta instrucción salvo que sea absolutamente necesario.';
$string['action:generate_image:endpoint'] = 'Punto final de la API';
$string['action:generate_image:model'] = 'Modelo de IA';
$string['action:generate_image:model_help'] = 'El modelo utilizado para generar imágenes a partir de las peticiones del usuario.';
$string['action:generate_text:endpoint'] = 'Punto final de la API';
$string['action:generate_text:model'] = 'Modelo de IA';
$string['action:generate_text:model_help'] = 'El modelo utilizado para generar la respuesta de texto.';
$string['action:generate_text:systeminstruction'] = 'Instrucción del sistema';
$string['action:generate_text:systeminstruction_default'] = 'Genera texto a partir de la petición del usuario. Devuelve el texto generado solo en texto plano, en el mismo idioma que la petición. Sin markdown, saludos ni fórmulas de cortesía.';
$string['action:generate_text:systeminstruction_help'] = 'Esta instrucción se envía al modelo de IA junto con la petición del usuario. No se recomienda editar esta instrucción salvo que sea absolutamente necesario.';
$string['action:summarise_text:endpoint'] = 'Punto final de la API';
$string['action:summarise_text:model'] = 'Modelo de IA';
$string['action:summarise_text:model_help'] = 'El modelo utilizado para resumir el texto proporcionado.';
$string['action:summarise_text:systeminstruction'] = 'Instrucción del sistema';
$string['action:summarise_text:systeminstruction_default'] = 'Resume el texto del usuario. Devuelve el resumen solo en texto plano, en el mismo idioma que el texto de entrada. Sin markdown, saludos ni fórmulas de cortesía.';
$string['action:summarise_text:systeminstruction_help'] = 'Esta instrucción se envía al modelo de IA junto con la petición del usuario. No se recomienda editar esta instrucción salvo que sea absolutamente necesario.';
$string['apikey'] = 'Clave de API de xAI';
$string['apikey_help'] = 'Obtenga una clave en la <a href="https://console.x.ai/" target="_blank">consola de xAI</a>.';
$string['custom_model_name'] = 'Nombre del modelo personalizado';
$string['extraparams'] = 'Parámetros adicionales';
$string['extraparams_help'] = 'Aquí se pueden configurar parámetros adicionales. Se admite el formato JSON. Por ejemplo:
<pre>
{
    "temperature": 0.5,
    "max_completion_tokens": 100
}
</pre>
Los modelos de razonamiento de Grok no aceptan frequency_penalty ni presence_penalty. max_tokens está en desuso; utilice max_completion_tokens.';
$string['invalidjson'] = 'Cadena JSON no válida';
$string['pluginname'] = 'Proveedor de API de xAI';
$string['privacy:metadata'] = 'El complemento proveedor de API de xAI no almacena ningún dato personal.';
$string['privacy:metadata:aiprovider_xai:externalpurpose'] = 'Esta información se envía a la API de xAI para que se genere una respuesta. Los ajustes de su cuenta de xAI pueden cambiar cómo xAI almacena y conserva estos datos. Este complemento no envía datos de usuario de forma explícita a xAI ni los almacena en Moodle LMS.';
$string['privacy:metadata:aiprovider_xai:model'] = 'El modelo utilizado para generar la respuesta.';
$string['privacy:metadata:aiprovider_xai:numberimages'] = 'Al generar imágenes, el número de imágenes utilizado en la respuesta.';
$string['privacy:metadata:aiprovider_xai:prompttext'] = 'El texto de la petición introducido por el usuario y utilizado para generar la respuesta.';
$string['privacy:metadata:aiprovider_xai:responseformat'] = 'El formato de la respuesta al generar imágenes.';
$string['settings'] = 'Ajustes';
$string['settings_help'] = 'Ajuste las opciones siguientes para personalizar cómo se envían las solicitudes a xAI.';
$string['settings_max_completion_tokens'] = 'max_completion_tokens';
$string['settings_max_completion_tokens_help'] = 'El número máximo de tokens utilizados en el texto generado. Este límite se aplica a los tokens de salida visibles.';
$string['settings_resolution'] = 'resolution';
$string['settings_resolution_1k'] = '1k';
$string['settings_resolution_2k'] = '2k';
$string['settings_resolution_help'] = 'Resolución de salida de las imágenes generadas. 1k es el valor predeterminado de la API. 2k solicita una imagen de mayor resolución. 1.5k puede enviarse como parámetro adicional personalizado.';
$string['settings_temperature'] = 'temperature';
$string['settings_temperature_help'] = 'Temperatura de muestreo entre 0 y 2. Los valores altos hacen que el resultado sea más aleatorio. Los valores bajos lo hacen más preciso.';
$string['settings_top_p'] = 'top_p';
$string['settings_top_p_help'] = 'top_p (muestreo nucleus) determina cuántas palabras posibles se tienen en cuenta. Un valor alto (por ejemplo, 0,9) significa que el modelo considera más palabras, lo que hace que el texto generado sea más diverso.';
