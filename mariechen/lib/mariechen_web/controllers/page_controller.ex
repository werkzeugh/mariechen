defmodule EvablutWeb.PageController do
  use EvablutWeb, :controller

  def index(conn, _params) do
    render(conn, "index.html")
  end
end
