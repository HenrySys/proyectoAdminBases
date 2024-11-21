import { HttpClient, HttpHeaders } from "@angular/common/http";
import { Injectable } from "@angular/core";
import { server } from "./global";
import { Empleado } from "../models/empleado";
import { Observable } from "rxjs";

@Injectable({
  providedIn: 'root'
})
export class EmpleadoService {
    public url:string;
    constructor(private _http:HttpClient){
        this.url = server.url;
    }

    store(data:any):Observable<any>{
        let userJson = JSON.stringify(data);
        let params = 'data='+userJson;
        let headers = new HttpHeaders().set('Content-Type','application/x-www-form-urlencoded');
        let options = {headers};
        return this._http.post(this.url+'empleado/store',params,options);
    }

    update(emplado:Empleado):Observable<any>{
        let userJson = JSON.stringify(emplado);
        let params = 'data='+userJson;
        let headers;
      let bearerToken = sessionStorage.getItem('token');
      if(bearerToken){
          headers = new HttpHeaders().set('Content-Type','application/x-www-form-urlencoded').set('bearertoken',bearerToken);
      }else{
          headers = new HttpHeaders().set('Content-Type','application/x-www-form-urlencoded');
      }
      let options = {headers};
        return this._http.post(this.url+'emplado/update/'+emplado.id,params,options);
    }

    delete(id: number): Observable<any> {
      let headers;
      let bearerToken = sessionStorage.getItem('token');
      if(bearerToken){
          headers = new HttpHeaders().set('Content-Type','application/x-www-form-urlencoded').set('bearertoken',bearerToken);
      }else{
          headers = new HttpHeaders().set('Content-Type','application/x-www-form-urlencoded');
      }
      let options = {headers};

      return this._http.delete(this.url + 'emplado/' + id,options);
    }

    uploadImage(image: File): Observable<any> {
      const formularioDatos: FormData = new FormData();
      formularioDatos.append('file0', image, image.name);

      let headers = new HttpHeaders();
      const bearerToken = sessionStorage.getItem('token');

      if (bearerToken) {
        headers = headers.set('bearertoken', `${bearerToken}`);
      }

      return this._http.post(this.url + 'vehiculo/upload', formularioDatos, { headers });
    }

  // Método para obtener una imagen
  getImage(filename: string): Observable<Blob> {
    debugger;
    let headers;
    let bearerToken = sessionStorage.getItem('token');
    if (bearerToken) {
        headers = new HttpHeaders().set('Content-Type', 'application/x-www-form-urlencoded').set('bearertoken', bearerToken);
    } else {
        headers = new HttpHeaders().set('Content-Type', 'application/x-www-form-urlencoded');
    }
    let options = { headers, responseType: 'blob' as 'json' };
    return this._http.get<Blob>(`${this.url}vehiculo/getimage/${filename}`, options);
    }

    getEmpleados():Observable<any>{
      let headers;
      let bearerToken = sessionStorage.getItem('token');
      if(bearerToken){
          headers = new HttpHeaders().set('Content-Type','application/x-www-form-urlencoded').set('bearertoken',bearerToken);
      }else{
          headers = new HttpHeaders().set('Content-Type','application/x-www-form-urlencoded');
      }
      let options = {headers};
      return this._http.get(this.url+'empleado/',options);
    }

    getEmpleado(id:number):Observable<any>{
      let headers;
      let bearerToken = sessionStorage.getItem('token');
      if(bearerToken){
          headers = new HttpHeaders().set('Content-Type','application/x-www-form-urlencoded').set('bearertoken',bearerToken);
      }else{
          headers = new HttpHeaders().set('Content-Type','application/x-www-form-urlencoded');
      }
      let options = {headers};

      return this._http.get(this.url+'emplado/'+id,options);
    }
}
