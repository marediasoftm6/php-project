<div class="container">
    <h1 class="heading-center">Ask A Question</h1>
    <form method="post" action="./server/requests.php">
        <div class="col-6 offset-sm-3 margin-bottom-15">
            <label for="username">Title</label>
            <input type="text" name="title" class="form-control" id="title" placeholder="Enter title!">
        </div>

        <div class="col-6 offset-sm-3 margin-bottom-15">
            <label for="emailaddress">Description</label>
            <textarea name="description" class="form-control" id="description" placeholder="Write description here!"></textarea>
        </div>

        <div class="col-6 offset-sm-3 margin-bottom-15">
            <label for="username" class="form-label" >Category</label>
            <select name="category" class="form-control" id="category">
                <option value="">General</option>
                <option value="">IT</option>
                <option value="">Automobiles</option>
                <option value="">Food</option>
                <option value="">Animals</option>
                <option value="">Science</option>
            </select>
        </div>

        <div class="col-6 offset-sm-3 d-flex justify-content-between align-items-center">
            <button type="submit" name="submit" class="btn btn-primary">Submit</button>
        </div>
    </form>
</div>