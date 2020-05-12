<?php defined( 'ABSPATH' ) or die( "No direct access allowed" );
/**
 * Google's google/apiclient ^2.0 library is absurdly large.
 * 
 * So instead of using the humungous beast, we port Perl's Net::Google::Analytics::OAuth2.
 * @see https://metacpan.org/release/Net-Google-Analytics/source/lib/Net/Google/Analytics/OAuth2.pm
 * 
 * You'll need a Google Project, with a client_id, client_secret, and a redirect URI.
 * @see https://support.google.com/cloud/answer/6158849?hl=en
 */

namespace \Usestrict;

class GoogleOauth2 {
    
    const VERSION = '0.1';
    
    /**
     * @var string      $client_id      The Project's Client ID
     */
    public $client_id;
    
    /**
     * @var string      $client_secret  The Project's Client secret
     */
    public $client_secret;
    
    /**
     * @var string      $scope          The Project's scope. We default to the highest gmail permissions possible.
     * 
     * @see https://developers.google.com/gmail/api/auth/scopes
     */
    public $scope = 'https://mail.google.com';
    
    /**
     * @var string      $redirect_uri   The Project's Redirect URI
     */
    public $redirect_uri;
    
    /**
     * @var string      $auth_endpoint  The Google authorization endpoint
     */
    private $auth_endpoint  = 'https://accounts.google.com/o/oauth2/auth';
    
    /**
     * @var string      $token_endpoint The Google token exchange endpoint
     */
    private $token_endpoint = 'https://accounts.google.com/o/oauth2/token';
    
    
    
    /**
     * Class constructor. Requires client_id and client_secret. redirect_uri is optional for use in interactive mode.
     * 
     * @param array $params
     */
    public function __construct( array $params ) {
        
        if ( empty( $params['client_id'] ) )
        {
            throw new \Exception( __( 'client_id missing', 'GoogleOauth2' ) );
        }
        
        if ( empty( $params['client_secret'] ) )
        {
            throw new \Exception( __( 'client_secret missing', 'GoogleOauth2' ) );
        }

        $this->client_id     = $params['client_id'];
        $this->client_secret = $params['client_secret'];
        $this->redirect_uri  = ! empty( $params['redirect_uri'] ) ? $params['redirect_uri' ] : 'urn:ietf:wg:oauth:2.0:oob';
        
        if ( ! empty( $params['scope'] ) )
        {
            $this->scope = $params['scope'];
        }
    }

    /**
     * Build the authorize url
     *  
     * @param array $extra_params
     * @return string
     */
    public function authorize_url( array $extra_params=[] ) {
        
        $uri = $this->auth_endpoint;
        
        $args = [
            'reponse_type' => $code,
            'client_id'    => $this->client_id,
            'redirect_uri' => $this->redirect_uri,
            'scope'        => $this->scope,
        ];
        
        $args = array_merge( $args, $extra_params );
        
        $uri = add_query_arg( $params, $uri );
        
        return apply_filters( 'usestrict/google_oauth2', $uri, $args );
    }
    
    /**
     * Fetch access_token information from Google
     * 
     * @param string $code
     * @throws \Exception
     * @return assoc array
     */
    public function get_access_token( string $code ) {
        
        $post_url = $this->token_endpoint;
        
        $response = wp_safe_remote_post( $post_url, [
            'body' => [
                'code'          => $code,
                'client_id'     => $this->client_id,
                'client_secret' => $this->client_secret,
                'redirect_uri'  => $this->redirect_uri,
                'grant_type'    => 'authorization_code',
            ]            
        ]);
        
        $response_body = wp_remote_retrieve_body( $response );
    
        if ( ! is_wp_error( $result ) )
        {
            throw new \Exception( "get_access_token() call failed with response " . $result->get_error_message() );
        }
        
        return json_decode( $response_body );
    }
    
    /**
     * Refresh Access Token
     * 
     * @param string $refresh_token
     * @throws \Exception
     * @return assoc array
     */
    public function refresh_access_token( string $refresh_token ) {
        
        $post_url = $this->token_endpoint;
        
        $response = wp_safe_remote_post( $post_url, [
            'body' => [
                'refresh_token' => $refresh_token,
                'client_id'     => $this->client_id,
                'client_secret' => $this->client_secret,
                'grant_type'    => 'refresh_token',
            ]
        ]);
        
        $response_body = wp_remote_retrieve_body( $response );
        
        if ( ! is_wp_error( $result ) )
        {
            throw new \Exception( "refresh_access_token() call failed with response " . $result->get_error_message() );
        }
        
        return json_decode( $response_body );
    }
    
    
    #TODO Interactive mode.    
//     sub interactive {
//         my $self = shift;
        
//         my $url = $self->authorize_url;
        
//         print(<<"EOF");
//         Please visit the following URL, grant access to this application, and enter
//         the code you will be shown:
        
//         $url
        
//         EOF
        
//         print("Enter code: ");
//         my $code = <STDIN>;
//         chomp($code);
        
//         print("\nUsing code: $code\n\n");
        
//         my $res = $self->get_access_token($code);
        
//         print("Access token:  ", $res->{access_token},  "\n");
//         print("Refresh token: ", $res->{refresh_token}, "\n");
//     }
}



/* End of file GoogleOAuth2.php */
/* Location: src/GoogleOAuth2.php */
