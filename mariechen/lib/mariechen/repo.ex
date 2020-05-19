defmodule Evablut.Repo do
  use Ecto.Repo,
    otp_app: :evablut,
    adapter: Ecto.Adapters.MySQL
end
