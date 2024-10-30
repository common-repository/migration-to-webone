<?php

namespace Webone\Webone\Handlers;

use WP_REST_Request;
use WP_REST_Response;

class RestApiHandler
{
    public function restApiInit()
    {
        register_rest_route(WEBONE_REST_API_NAMESPACE, '/info'               , ['methods' => 'GET', 'callback' => [$this, 'getInfo'              ], 'permission_callback' => '__return_true']);
        register_rest_route(WEBONE_REST_API_NAMESPACE, '/posts'              , ['methods' => 'GET', 'callback' => [$this, 'getPosts'             ], 'permission_callback' => '__return_true']);
        register_rest_route(WEBONE_REST_API_NAMESPACE, '/posts/categories'   , ['methods' => 'GET', 'callback' => [$this, 'getPostsCategories'   ], 'permission_callback' => '__return_true']);
        register_rest_route(WEBONE_REST_API_NAMESPACE, '/products'           , ['methods' => 'GET', 'callback' => [$this, 'getProducts'          ], 'permission_callback' => '__return_true']);
        register_rest_route(WEBONE_REST_API_NAMESPACE, '/products/categories', ['methods' => 'GET', 'callback' => [$this, 'getProductsCategories'], 'permission_callback' => '__return_true']);
    }

    public function getInfo()
    {
        return new WP_REST_Response([
            'version' => WEBONE_PLUGIN_VERSION
        ]);
    }

    public function getPostsCategories($request)
    {
        $page = 1;
        if (isset($request['page']) && $request['page'] > 0) {
            $page = (int) $request['page'];
        }

        $per_page = 10;
        if (isset($request['per_page']) && $request['per_page'] > 0) {
            $per_page = (int) $request['per_page'];
        }

        $request    = new WP_REST_Request('GET', '/wp/v2/categories');
        $request->set_query_params(['page' => $page, 'per_page' => $per_page]);
        $response   = rest_do_request($request);
        $server     = rest_get_server();
        $categories = $server->response_to_data($response, false);

        return new WP_REST_Response($categories);
    }

    public function getPosts($request)
    {
        $page = 1;
        if (isset($request['page']) && $request['page'] > 0) {
            $page = (int) $request['page'];
        }

        $per_page = 10;
        if (isset($request['per_page']) && $request['per_page'] > 0) {
            $per_page = (int) $request['per_page'];
        }

        $request  = new WP_REST_Request('GET', '/wp/v2/posts');
        $request->set_query_params(['page' => $page, 'per_page' => $per_page]);
        $response = rest_do_request($request);
        $server   = rest_get_server();
        $posts     = $server->response_to_data($response, false);

        foreach ($posts as $key => $post) {
            $posts[$key]['webone_featured_media'] = $this->get_featured_media($post['featured_media']);
            $posts[$key]['webone_categories']     = $this->get_post_categories($post['categories']);
            $posts[$key]['webone_tags']           = $this->get_post_tags($post['id']);
        }

        return new WP_REST_Response($posts);
    }

    public function getProductsCategories($request)
    {
        $page = 1;
        if (isset($request['page']) && $request['page'] > 0) {
            $page = (int) $request['page'];
        }

        $per_page = 10;
        if (isset($request['per_page']) && $request['per_page'] > 0) {
            $per_page = (int) $request['per_page'];
        }

        add_filter('woocommerce_rest_check_permissions', '__return_true');
        $request  = new WP_REST_Request('GET', '/wc/v3/products/categories');
        $request->set_query_params(['page' => $page, 'per_page' => $per_page]);
        $response = rest_do_request($request);
        remove_filter('woocommerce_rest_check_permissions', '__return_true');
        $server   = rest_get_server();
        $categories = $server->response_to_data($response, false);

        return new WP_REST_Response($categories);
    }

    public function getProducts($request)
    {
        $page = 1;
        if (isset($request['page']) && $request['page'] > 0) {
            $page = (int) $request['page'];
        }

        $per_page = 10;
        if (isset($request['per_page']) && $request['per_page'] > 0) {
            $per_page = (int) $request['per_page'];
        }

        add_filter('woocommerce_rest_check_permissions', '__return_true');
        $request  = new WP_REST_Request('GET', '/wc/v3/products');
        $request->set_query_params(['page' => $page, 'per_page' => $per_page]);
        $response = rest_do_request($request);
        remove_filter('woocommerce_rest_check_permissions', '__return_true');
        $server   = rest_get_server();
        $products = $server->response_to_data($response, false);

        $wcCurrency = get_woocommerce_currency();

        foreach ($products as $key => $product) {
            $products[$key]['webone_currency'] = $wcCurrency;
            if ('variable' == $product['type']) {
                $products[$key]['webone_variations'] = $this->get_variations($product['id']);
            } else {
                $products[$key]['webone_variations'] = [];
            }
        }

        return new WP_REST_Response($products);
    }

    public function get_variations($product)
    {
        $product = wc_get_product($product);
        $variations =$product->get_available_variations();
        $formatedVariations = [];
        foreach ($variations as $value) {
            $variation = [];
            if (is_array($value['attributes'])) {
                foreach ($value['attributes'] as $kat => $at) {
                    if (!empty($at)) {
                        $exploded = explode('_', $kat);
                        // attribute_pa_{kat} || attribute_{kat}
                        if (count($exploded) > 2) {
                            $variation['attributes'][] = [
                                'name'   => urldecode(explode('_', $kat)[2]),
                                'option' => urldecode($at)
                            ];
                        } else {
                            $variation['attributes'][] = [
                                'name'   => urldecode(explode('_', $kat)[1]),
                                'option' => urldecode($at)
                            ];
                        }
                    }
                }
            } else {
                $variation['attributes'] = [];
            }

            $formatedVariations[] = [
                'attributes'    => $variation['attributes'],
                'price'         => $value['display_price'],
                'regular_price' => $value['display_regular_price'],
                'sale_price'    => $value['display_price'],
            ];
        }

        return $formatedVariations;
    }

    protected function get_featured_media($id) {
        $attachment = wp_get_attachment_image_src($id, 'full');
        if (!is_array($attachment)) {
            return '';
        }

        return $attachment[0];
    }

    public function get_post_categories($catIds)
    {
        $categories = [];
        foreach ($catIds as $categoryId) {
            $category = get_category($categoryId);
            $categories[] = [
                'id'          => $category->term_id,
                'description' => $category->description,
                'name'        => $category->name,
                'slug'        => urldecode($category->slug),
                'parent'      => $category->parent
            ];
        }

        return $categories;
    }

    public function get_post_tags($id)
    {
        $tags_terms = get_the_tags($id);
        $tags = [];
        if (is_array($tags_terms)) {
            foreach ($tags_terms as $term) {
                $tags[] = [
                    'id'          => $term->term_id,
                    'description' => $term->description,
                    'name'        => $term->name,
                    'slug'        => urldecode($term->slug),
                ];
            }
        } else {
            $tags = [];
        }

        return $tags;
    }
}
