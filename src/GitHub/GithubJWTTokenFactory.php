<?php declare(strict_types=1);

namespace Rector\RectorCI\GitHub;

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Token;

final class GithubJWTTokenFactory
{
    /**
     * @var string
     */
    private $githubAppId;

    /**
     * @var string
     */
    private $githubAppPrivateKey;

    public function __construct(string $githubAppId, string $githubAppPrivateKey)
    {
        $this->githubAppId = $githubAppId;
        $this->githubAppPrivateKey = $githubAppPrivateKey;
    }

    public function create(): Token
    {
        return (new Builder())
            ->issuedBy($this->githubAppId)
            ->issuedAt(time())
            ->expiresAt(time() + 60)
            ->getToken(new Sha256(), new Key($this->githubAppPrivateKey));
    }
}
