<?php

namespace ClaudioDekker\LaravelAuthBlade\Testing;

use ClaudioDekker\LaravelAuthBlade\Testing\Partials\AccountRecoveryChallengeViewTests;
use ClaudioDekker\LaravelAuthBlade\Testing\Partials\AccountRecoveryRequestViewTests;
use ClaudioDekker\LaravelAuthBlade\Testing\Partials\CredentialsOverviewViewTests;
use ClaudioDekker\LaravelAuthBlade\Testing\Partials\LoginViewTests;
use ClaudioDekker\LaravelAuthBlade\Testing\Partials\MultiFactorChallengeViewTests;
use ClaudioDekker\LaravelAuthBlade\Testing\Partials\RegisterPublicKeyCredentialViewTests;
use ClaudioDekker\LaravelAuthBlade\Testing\Partials\RegisterTotpCredentialViewTests;
use ClaudioDekker\LaravelAuthBlade\Testing\Partials\RegisterViewTests;
use ClaudioDekker\LaravelAuthBlade\Testing\Partials\SudoModeChallengeViewTests;

trait BladeViewTests
{
    use RegisterViewTests;
    use LoginViewTests;
    use AccountRecoveryRequestViewTests;

    // Challenges
    use AccountRecoveryChallengeViewTests;
    use MultiFactorChallengeViewTests;
    use SudoModeChallengeViewTests;

    // Settings
    use CredentialsOverviewViewTests;
    use RegisterPublicKeyCredentialViewTests;
    use RegisterTotpCredentialViewTests;
}
