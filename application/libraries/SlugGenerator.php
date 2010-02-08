<?php

class SlugGenerator
{
  protected $collection;

  public function __construct( Collection $collection )
  {
    $this->collection = $collection;
  }

  public function toSlug( $string )
  {
    $max_length = $this->collection->getConfig( 'slug_max_length' );
    $spacer     = $this->collection->getConfig( 'slug_spacer' );

    if ( ! $max_length || ! is_int( $max_length ) ) {
      $max_length = 30;
    }

    if ( ! $spacer || ! is_string( $spacer ) ) {
      $spacer = '-';
    }

    if (function_exists('iconv')) {
      $string = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $string);
    }

    $slug = strtolower( $string );
    $slug = preg_replace( '/[^a-z0-9-]/', $spacer, $slug );
    $slug = trim( $slug, $spacer );
    $slug = preg_replace( "/$spacer+/", $spacer, $slug );
    $slug = $this->stripStopwords( $slug );

    if ( $max_length && is_int( $max_length ) ) {
      if (
        strlen( $slug ) > $max_length
        && substr( $slug, $max_length, 1 ) != '-'
      ) {
        // chopped in middle of word
        preg_match( "/^ .{0,$max_length} (?=-) /x", $slug, $matches );
        $slug = $matches[0];
      }
      else {
        $slug = substr( $slug, 0, $max_length );
      }
    }

    return $slug;
  }

  protected function stripStopwords( $string )
  {
    $stopwords = $this->collection->getConfig( 'slug_stopwords' );

    if ( is_string( $stopwords ) ) {
      $stopwords = array( $stopwords );
    }

    if ( ! $stopwords || ! is_array( $stopwords ) ) {
      $stopwords = array(
        'an',
        'a',
        'the',
        'of',
        'and',
      );
    }

    $pattern = '/\b(' . implode( '|', $stopwords ) . ')-?\b/';

    return preg_replace( $pattern, '', $string );
  }
}