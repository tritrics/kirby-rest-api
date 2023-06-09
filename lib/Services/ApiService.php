<?php

namespace Tritrics\Api\Services;

use Kirby\Cms\Response;
use Tritrics\Api\Data\Collection;

class ApiService
{
  /**
   * @return bool 
   */
  public static function isEnabledLanguages ()
  {
    return self::isEnabled('languages');
  }

  /**
   * @return bool 
   */
  public static function isEnabledSite ()
  {
    return self::isEnabled('site');
  }

  /**
   * @return bool 
   */
  public static function isEnabledNode ()
  {
    return self::isEnabled('node');
  }

  /**
   * @return bool 
   */
  public static function isEnabledChildren ()
  {
    return self::isEnabled('children');
  }

  /**
   * @return bool 
   */
  public static function isEnabledSubmit ()
  {
    return self::isEnabled('submit');
  }

  /**
   * Helper: Find a page by translated slug
   * (Kirby can only find by default slug)
   * @param mixed $lang 
   * @param mixed $slug 
   * @return mixed 
   */
  public static function findPageBySlug ($lang, $slug)
  {
    if (kirby()->multilang()) {
      $pages = kirby()->site()->pages();
      $keys = explode('/', trim($slug, '/'));
      return self::findPageBySlugRec($pages, $lang, $keys);
    } else {
      return page($slug);
    }
  }

  /** */
  public static function initResponse()
  {
    $Request = kirby()->request();
    $res = new Collection();
    $res->add('ok', true);
    $res->add('status', 200);
    $res->add('url', $Request->url()->toString());
    return $res;
  }

  /**
   * Response OK
   * @param string $msg 
   * @return Response 
   */
  public static function ok ( $msg = 'OK' )
  {
    return Response::json([ 'status' => 200, 'msg' => $msg ], 200);
  }

  /**
   * Shortcut for Bad Request
   * @return Response 
   */
  public static function invalidLang ()
  {
    return self::badRequest ('Given language is not valid');
  }

  /**
   * Bad Request
   * @param string $msg 
   * @return Response 
   */
  public static function badRequest ( $msg = 'Bad Request' )
  {
    return Response::json([ 'status' => 400, 'msg' => $msg ], 400);
  }

  /**
   * API is diabled
   * @param string $msg 
   * @return Response 
   */
  public static function disabled ( $msg = 'API is disabled for this action')
  {
    return Response::json([ 'status' => 403, 'msg' => $msg ], 403);
  }

  /**
   * Not found
   * @param string $msg 
   * @return Response 
   */
  public static function notFound ( $msg = 'Page is not found' )
  {
    return Response::json([ 'status' => 404, 'msg' => $msg ], 404);
  }

  /**
   * Not Allowed
   * @param string $msg 
   * @return Response 
   */
  public static function notAllowed ( $msg = 'Action not allowed' )
  {
    return Response::json([ 'status' => 405, 'msg' => $msg ], 405);
  }

  /**
   * Internal Server Error
   * @param string $msg 
   * @return Response 
   */
  public static function fatal ( $msg = 'Internal Server Error' )
  {
    return Response::json([ 'status' => 500, 'msg' => $msg ], 500);
  }

  /**
   * Check, if API's functions are enabled
   * @param string $method post|get
   * @return bool 
   */
  private static function isEnabled ($method)
  {
    $global = kirby()->option('tritrics.restapi.enabled', false);
    $setting = kirby()->option('tritrics.restapi.enabled.' . $method, false);
    return $global === true || $setting === true;
  }

  /**
   * Subfunction of findPageBySlug
   * @param mixed $collection 
   * @param mixed $lang 
   * @param mixed $keys 
   * @return mixed 
   */
  private static function findPageBySlugRec ($collection, $lang, $keys)
  {
    $key = array_shift($keys);
    foreach ($collection as $page) {
      if ($page->slug($lang) === $key) {
        if (count($keys) > 0) {
          return self::findPageBySlugRec($page->children(), $lang, $keys);
        } else {
          return $page;
        }
      }
    }
    return null;
  }
}
