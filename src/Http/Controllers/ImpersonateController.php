<?php

namespace HashmatWaziri\LaravelMultiAuthImpersonate\Http\Controllers;

use HashmatWaziri\LaravelMultiAuthImpersonate\Services\ImpersonateManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ImpersonateController extends Controller
{
    /** @var ImpersonateManager */
    protected $manager;

    /**
     * ImpersonateController constructor.
     */
    public function __construct()
    {
        $this->manager = app()->make(ImpersonateManager::class);
        session()->remove('guardName');
        if (\request()->routeIs('impersonate')) {
            session()->put(['guardName' => request()->guardName]);
        }
    }

    /**
     * @param int         $id
     * @param string|null $guardName
     * @return  RedirectResponse
     * @throws  \Exception
     */
    public function take(Request $request, $id, $guardName = null)
    {
        $guardName = $guardName ?? $this->manager->getDefaultSessionGuard();



        // Cannot impersonate yourself
        if ($id == auth()->guard('employee')->user()->getAuthIdentifier() && ($this->manager->getCurrentAuthGuardName() == $guardName)) {
            abort(403);
        }

        // Cannot impersonate again if you're already impersonate a user
        if ($this->manager->isImpersonating()) {
            abort(403);
        }




        if (! $request->user($this->manager->getCurrentAuthGuardName())->canImpersonate()) {
            abort(403);
        }

        $userToImpersonate = $this->manager->findUserById($id, $guardName);



        if ($userToImpersonate->canBeImpersonated()) {
            if ($this->manager->take($request->user($this->manager->getCurrentAuthGuardName()), $userToImpersonate, $guardName)) {
                $takeRedirect = $this->manager->getTakeRedirectTo($userToImpersonate);

                if ($takeRedirect !== 'back') {
                    return redirect()->to($takeRedirect);
                }
            }
        }

        return redirect()->back();
    }

    /**
     * @return RedirectResponse
     */
    public function leave()
    {
        if (! $this->manager->isImpersonating()) {
            abort(403);
        }

        $leaveRedirect = $this->manager->getLeaveRedirectTo();
        $this->manager->leave();


        if ($leaveRedirect !== 'back') {
            return redirect()->to($leaveRedirect);
        }

        return redirect()->back();
    }
}
