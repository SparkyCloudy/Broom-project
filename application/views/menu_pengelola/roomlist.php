<h1>Ruangan</h1>

<div>
  <!-- Button trigger modal -->
  <button type="button" class="btn btn-primary mb-4" data-bs-toggle="modal" data-bs-target="#exampleModal">
    Tambah Ruangan
  </button>

  <!-- Modal -->
  <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="exampleModalLabel">Tambah Ruangan</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        
        <?= form_open_multipart('rooms/add', 'id="form-reservasi"') ?>
          <div class="modal-body">
            <div class="mb-3">
              <label for="inputRoomName" class="form-label">Nama</label>
              <input type="text" class="form-control" id="inputRoomName"
                     aria-describedby="emailHelp" name="name"
                     placeholder="Nama gedung/ruangan">
            </div>

            <div class="mb-3">
              <label class="form-label" for="inputRoomStatus">Status</label>
              <select class="form-select" id="inputRoomStatus"
                      name="status">
                <option value="" selected>Pilihan</option>
                <option value="1">Tersedia</option>
                <option value="0">Tidak Tersedia</option>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label" for="inputRoomImage">Gambar</label>
              <input type="file" class="form-control" id="inputRoomImage"
                     name="image">
            </div>

            <div class="mb-3">
              <label for="inputRoomDescription" class="form-label">Deskripsi</label>
              <textarea placeholder="Deskripsi ruangan/gedung yang disewakan"
                        class="form-control" id="inputRoomDescription"
                        name="description"></textarea>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Save changes</button>
          </div>
        <?= form_close() ?>
      </div>
    </div>
  </div>

<!-- Search bar with dropdown -->
  <div class="input-group mb-3 dropdown-content">
    <span class="input-group-text" id="basic-addon1">Search</span>
    <input id="inputSearch" type="search" placeholder="Ruangan"
           aria-label="SearchRuangan" aria-describedby="basic-addon1"
           onkeyup="showResult()" autocomplete="off" autocapitalize="off"
           class="form-control dropdown-toggle" data-bs-toggle="dropdown"
           aria-expanded="false" data-bs-auto-close="outside">
    <ul id="livesearch" class="dropdown-menu">
      <li><a class="dropdown-item" href="">No Suggestion</a></li>
    </ul>
  </div>
  
  <div class="d-flex justify-content-between">
	  <?php
	  view_data($hasil);
	  $no = 1;
	  foreach ($hasil as $data):
		  ?>
        <div class="card" style="width: 18rem;">
          <img src="<?= base_url('assets/images/ruangan/'.$data->image) ?>"
               class="card-img-top" alt="...">
          <div class="card-body">
            <h5 class="card-title">
                <?= $data->name ?>
            </h5>

            <p class="card-text">
                <?= $data->description ?>
            </p>
            
          </div>
          
          <div class="card-footer">
            <a href="<?= site_url('rooms/detailrooms?id=' . $data->id); ?>"
               class="btn btn-primary">Lihat Detail</a>
          </div>
        </div>
		  <?php
		  $no++;
	  endforeach;
	  ?>
  </div>
</div>

<script src="<?= base_url('js/livesearch/search.js') ?>"></script>
