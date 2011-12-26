<?php
/*!
 * @file
 * Contains FileManager Class
 * @author Anders Evenrud <andersevenrud@gmail.com>
 * @license GPLv3 (see http://www.gnu.org/licenses/gpl-3.0.txt)
 * @created 2011-06-16
 */

/**
 * ApplicationFileManager Class
 *
 * @author  Anders Evenrud <andersevenrud@gmail.com>
 * @package OSjs.Applications
 * @class
 */
class ApplicationFileManager
  extends Application
{

  /**
   * Create a new instance
   */
  public function __construct() {
    parent::__construct();
  }

  public static function Event($uuid, $action, Array $args) {
    if ( $action == "browse" ) {
      $result = Array();

      $path  = $args['path'];
      $view  = $args['view'];
      $total = 0;
      $bytes = 0;
      $ignores = Array(".", ".gitignore");

      if ( $path == "/" ) {
        $ignores[] = "..";
      }

      if ( $view == "icon" ) {
        $columns = Array(
          Array(
            "className" => "Image",
            "style"     => null,
            "title"     => null
          ),
          Array(
            "className" => "Title",
            "style"     => null,
            "title"     => null
          ),
          Array(
            "className" => "Info",
            "style"     => "display:none;",
            "title"     => null
          )
        );
      } else {
        $columns = Array(
          Array(
            "className" => "Image Space First",
            "style"     => null,
            "title"     => "&nbsp;"
          ),
          Array(
            "className" => "Title Space",
            "style"     => null,
            "title"     => "Filename"
          ),
          Array(
            "className" => "Size Space",
            "style"     => null,
            "title"     => "Size"
          ),
          Array(
            "className" => "Type Space Last",
            "style"     => null,
            "title"     => "Type"
          ),
          Array(
            "className" => "Info",
            "style"     => "display:none;",
            "title"     => "&nbsp;"
          ),
        );
      }

      $files   = Array();
      if ( ($items = ApplicationVFS::ls($path, $ignores)) !== false ) {
        $i = 0;
        foreach ( $items as $file => $info ) {
          $icon = "/img/icons/32x32/{$info['icon']}";
          if ( preg_match("/^\/img/", $info['icon']) ) {
            $icon = $info['icon'];
          }

          $class = $i % 2 ? "odd" : "even";

          $files[] = Array(
            "icon"      => $icon,
            "type"      => $info["type"],
            "mime"      => $info["mime"],
            "name"      => $file,
            "path"      => $info["path"],
            "size"      => $info["size"],
            "class"     => $class,
            "protected" => $info["protected"]
          );

          $i++;

          $total++;
          $bytes += (int) $info['size'];
        }
      }

      return Array("items" => $files, "columns" => $columns, "total" => $total, "bytes" => $bytes, "path" => ($path == "/" ? "Home" : $path));
    } else if ( $action == "upload" ) {
      return true;
    }

    return false;
  }

}

?>
