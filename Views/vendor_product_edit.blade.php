@extends('vendor.vendor_dashboard')
@section('vendor')

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>

<div class="page-content">

<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Eladó</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">Termék szerkesztése</li>
            </ol>
        </nav>
    </div>
</div>  

<div class="card">
  <div class="card-body p-4">
      <h5 class="card-title">Termék szerkesztése</h5>
      <hr/>

      <form method="POST" action="{{ route('vendor.update.product') }}" id="myform">
          @csrf

          <input type="hidden" name="id" value="{{ $products->id }}" />
          <div class="form-body mt-4"> 
            <div class="row">
               <div class="col-lg-8">
               <div class="border border-3 p-4 rounded">

                <div class="mb-3">
                    <label for="inputProductTitle" class="form-label">Termék neve</label>
                    <input type="text" name="product_name" class="form-control" id="inputProductTitle" value="{{ htmlspecialchars($products->product_name, ENT_QUOTES, 'UTF-8') }}" required>
                </div>

                <div class="mb-3">
                    <label for="inputProductTags" class="form-label">Termék cimkék</label>
                    <input type="text" name="product_tags" class="form-control visually-hidden" data-role="tagsinput" value="{{ htmlspecialchars($products->product_tags, ENT_QUOTES, 'UTF-8') }}">
                </div>

                <!-- Additional fields omitted for brevity -->

                <div class="mb-3">
                    <label for="inputProductDescription" class="form-label">Termék rövid leírása</label>
                    <textarea class="form-control" name="short_desc" id="inputProductDescription" rows="3" required>{{ htmlspecialchars($products->short_desc, ENT_QUOTES, 'UTF-8') }}</textarea>
                </div>

                <div class="mb-3">
                    <label for="inputProductLongDescription" class="form-label">Termék hosszabb leírása</label>
                    <textarea id="mytextarea" name="long_desc" required>{{ htmlspecialchars($products->long_desc, ENT_QUOTES, 'UTF-8') }}</textarea>
                </div>

                <!-- Additional fields omitted for brevity -->

              </div>
             </div>
             <div class="col-lg-4">
              <div class="border border-3 p-4 rounded">
                <div class="row g-3">
                  <!-- Fields omitted for brevity -->
                  <div class="col-12">
                    <div class="d-grid">
                       <!-- Rejtett input mező a subcategory_slug értékhez -->
                        <input type="hidden" id="subcategory_slug" name="subcategory_slug" value="">
                       <input type="submit" class="btn btn-primary" value="Mentés" />
                    </div>
                  </div>
              </div> 
          </div>
        </div>
       </div>
      </div>
</form>
  </div>
</div>

<div class="page-content">
  <h6 class="mb-0 text-uppercase">Thumbnail fotó frissítése</h6>
  <hr>
  <div class="card">
    <form method="POST" action="{{ route('vendor.update.product.thumbnail') }}" enctype="multipart/form-data">
      @csrf
      <input type="hidden" name="id" value="{{ $products->id }}" />
      <input type="hidden" name="old_img" value="{{ htmlspecialchars($products->product_thumbnail, ENT_QUOTES, 'UTF-8') }}" />
      <div class="card-body">
        <div class="mb-3">
          <label for="formFile" class="form-label">Válassz thumbnail fotót</label>
          <input class="form-control" type="file" id="formFile" name="product_thumbnail" required>
        </div>					
        <div class="mb-3">				
          <img src="{{ asset($products->product_thumbnail) }}" style="width:100px; height:100px;" />
        </div>	
        <!-- Rejtett input mező a subcategory_slug értékhez -->
        <input type="hidden" id="subcategory_slug" name="subcategory_slug" value="">
        <input type="submit" class="btn btn-primary" value="Mentés" />
      </div>
    </form>
  </div>      
</div>

<div class="page-content">
  <h6 class="mb-0 text-uppercase">Termékfotók frissítése</h6>
  <div class="card">
    <div class="card-body">
      <table class="table mb-0">
        <thead>
          <tr>
            <th scope="col">#Sl</th>
            <th scope="col">Fotó</th>
            <th scope="col">Fotó változtatás</th>
            <th scope="col">Törlés</th>
          </tr> 
        </thead>
        <tbody>
          <form method="POST" action="{{ route('vendor.update.product.multiimage') }}" enctype="multipart/form-data">
           @csrf
           @foreach($multiImgs as $key => $img)
           <tr>
             <th scope="row">{{ $key+1 }}</th>
             <td><img src="{{ asset($img->photo_name) }}" style="width:70px; height:70px;" /></td>
             <td><input type="file" class="form-group" name="multi_img[{{ $img->id }}]" /></td>
             <td>
               <input type="submit" class="btn btn-primary" value="Fotó frissítése" />
               <a href="{{ route('vendor.product.multiimage.delete', $img->id) }}" class="btn btn-danger" id="delete">Törlés</a>
             </td>
           </tr>
           @endforeach
          </form>
        </tbody>
      </table>
    </div>
  </div>
</div>

@endsection
