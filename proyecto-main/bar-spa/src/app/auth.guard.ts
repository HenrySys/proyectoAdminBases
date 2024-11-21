import { Injectable } from '@angular/core';
import { CanActivateFn } from '@angular/router';
import { UserService } from './services/user.service';
import { Router } from '@angular/router';
import { inject } from '@angular/core';

@Injectable({
  providedIn: 'root'
})
export class AuthGuardService {
  constructor(private authService: UserService, private router: Router) {}

  canActivate(): boolean {
    if (this.authService.isLoggedIn()) {
      console.log(this.authService.expiredToken());
      return true;
    } else {
      this.router.navigate(['/']);
      return false;
    }
  }
}

export const AuthGuard: CanActivateFn = (
  route,
  state
) => {
  const authGuardService = inject(AuthGuardService);
  return authGuardService.canActivate();
};
