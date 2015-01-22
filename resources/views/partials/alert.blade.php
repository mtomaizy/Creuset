@if ( Session::has('alert') )
<div class="alert alert-dismissable alert-{{ Session::get('alert-class', 'info') }}"  role="alert">
	 <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	{{ Session::get('alert') }}
</div>
@endif